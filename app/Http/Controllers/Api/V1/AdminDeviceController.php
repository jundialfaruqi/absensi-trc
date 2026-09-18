<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminDeviceController extends Controller
{
    /**
     * Helper: Validasi bahwa user yang login memiliki role admin-opd atau super-admin.
     */
    protected function authorizeAdmin(Request $request): ?JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin-opd', 'super-admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Endpoint ini khusus untuk Admin OPD dan Super Admin.',
            ], 403);
        }
        return null;
    }

    /**
     * Helper: Cek apakah user memiliki hak akses level Super Admin (lintas OPD).
     */
    protected function isSuperAdmin(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'dev'])
            || $user->can('lihat-perangkat-all-opd')
            || $user->can('view-devices-all-opd')
            || $user->can('manage-devices');
    }

    /**
     * Helper: Cek apakah user berhak mengelola data perangkat tertentu.
     */
    protected function canManageDevice(User $user, Device $device): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $userOpdId = $user->opds()->first()?->id;
        return $userOpdId && (int)$device->opd_id === (int)$userOpdId;
    }

    /**
     * 1. GET /api/v1/admin/devices
     * Mengambil daftar perangkat dengan pagination, filter status, dan pencarian strictly berdasarkan nama perangkat.
     */
    public function index(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $isSuperAdmin = $this->isSuperAdmin($user);
        $userOpdId = $user->opds()->first()?->id;

        $query = Device::with(['opd:id,name,singkatan', 'personnel:id,name,nik,foto', 'user:id,name,email'])
            ->when(!$isSuperAdmin, function ($q) use ($userOpdId) {
                $q->where('opd_id', $userOpdId);
            });

        // Filter status (active, inactive, suspended)
        $status = $request->query('status');
        if (!empty($status) && in_array($status, ['active', 'inactive', 'suspended'])) {
            $query->where('status', $status);
        }

        // Pencarian: KHUSUS HANYA BERDASARKAN NAMA PERANGKAT
        $search = trim($request->query('search', ''));
        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Hitung statistik untuk OPD terkait
        $statsBaseQuery = Device::when(!$isSuperAdmin, function ($q) use ($userOpdId) {
            $q->where('opd_id', $userOpdId);
        });

        $totalCount = (clone $statsBaseQuery)->count();
        $activeCount = (clone $statsBaseQuery)->where('status', 'active')->count();
        $inactiveCount = (clone $statsBaseQuery)->where('status', 'inactive')->count();
        $suspendedCount = (clone $statsBaseQuery)->where('status', 'suspended')->count();

        $perPage = (int)$request->query('per_page', 10);
        $perPage = max(1, min(50, $perPage));

        $devices = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'devices' => $devices->items(),
                'stats' => [
                    'total' => $totalCount,
                    'active' => $activeCount,
                    'inactive' => $inactiveCount,
                    'suspended' => $suspendedCount,
                ],
                'pagination' => [
                    'current_page' => $devices->currentPage(),
                    'last_page' => $devices->lastPage(),
                    'per_page' => $devices->perPage(),
                    'total' => $devices->total(),
                    'has_more' => $devices->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * 2. GET /api/v1/admin/devices/form-options
     * Mengambil opsi dropdown untuk pembuatan & pengeditan perangkat.
     */
    public function formOptions(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $isSuperAdmin = $this->isSuperAdmin($user);
        $userOpdId = $user->opds()->first()?->id;

        // OPD options
        $opds = $isSuperAdmin
            ? Opd::select('id', 'name', 'singkatan')->orderBy('name')->get()
            : Opd::where('id', $userOpdId)->select('id', 'name', 'singkatan')->get();

        $deviceId = (int)$request->query('device_id', 0);
        $targetOpdId = $request->query('opd_id') ?: ($isSuperAdmin ? null : $userOpdId);

        // Personel yang belum memiliki perangkat atau sedang memakai perangkat ini
        $personnelQuery = Personnel::select('id', 'name', 'nik', 'opd_id')
            ->when($targetOpdId, function ($q) use ($targetOpdId) {
                $q->where('opd_id', $targetOpdId);
            })
            ->where(function ($q) use ($deviceId) {
                $q->whereDoesntHave('devices')
                    ->orWhereHas('devices', function ($dq) use ($deviceId) {
                        $dq->where('id', $deviceId);
                    });
            })
            ->orderBy('name');

        $personnels = $personnelQuery->get();

        // Daftar User Admin (untuk opsi pemegang tipe User)
        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        // Default auto-generated license key
        $generatedLicense = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));

        return response()->json([
            'status' => 'success',
            'data' => [
                'opds' => $opds,
                'personnels' => $personnels,
                'users' => $users,
                'generated_license_key' => $generatedLicense,
            ],
        ]);
    }

    /**
     * 3. GET /api/v1/admin/devices/{id}
     * Menampilkan detail perangkat.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $device = Device::with(['opd:id,name,singkatan', 'personnel:id,name,nik,foto', 'user:id,name,email'])->find($id);

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data perangkat tidak ditemukan.',
            ], 404);
        }

        if (!$this->canManageDevice($user, $device)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk melihat perangkat ini.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $device,
        ]);
    }

    /**
     * 4. POST /api/v1/admin/devices
     * Menambahkan perangkat & lisensi baru.
     */
    public function store(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $isSuperAdmin = $this->isSuperAdmin($user);
        $userOpdId = $user->opds()->first()?->id;

        $validator = Validator::make($request->all(), [
            'opd_id' => $isSuperAdmin ? 'required|exists:opds,id' : 'nullable',
            'holder_type' => 'required|in:personnel,user,manual',
            'personnel_id' => 'required_if:holder_type,personnel|nullable|exists:personnels,id',
            'user_id' => 'required_if:holder_type,user|nullable|exists:users,id',
            'holder_name' => 'required_if:holder_type,manual|nullable|string|max:255',
            'name' => 'required_if:holder_type,manual|nullable|string|max:255',
            'license_key' => 'required|string|max:50|unique:devices,license_key',
            'status' => 'required|in:active,inactive,suspended',
            'notes' => 'nullable|string|max:1000',
        ], [
            'holder_type.required' => 'Pilih jenis pemegang perangkat.',
            'personnel_id.required_if' => 'Pilih personel dari daftar.',
            'user_id.required_if' => 'Pilih admin dari daftar.',
            'holder_name.required_if' => 'Nama pemegang manual wajib diisi.',
            'name.required_if' => 'Nama perangkat wajib diisi jika input manual.',
            'license_key.required' => 'License key wajib diisi.',
            'license_key.unique' => 'License key sudah digunakan perangkat lain.',
            'status.required' => 'Pilih status awal perangkat.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $holderType = $request->input('holder_type');
        $personnelId = $holderType === 'personnel' ? $request->input('personnel_id') : null;
        $userId = $holderType === 'user' ? $request->input('user_id') : null;
        $holderName = $holderType === 'manual' ? $request->input('holder_name') : null;

        // Cek duplikasi personel device
        if ($holderType === 'personnel' && $personnelId) {
            $exists = Device::where('personnel_id', $personnelId)->exists();
            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Personel ini sudah memiliki perangkat terdaftar.',
                ], 422);
            }
        }

        // Tentukan OPD ID
        $opdId = $isSuperAdmin ? (int)$request->input('opd_id') : (int)$userOpdId;
        if ($holderType === 'personnel' && $personnelId) {
            $personnel = Personnel::find($personnelId);
            if ($personnel && $personnel->opd_id) {
                $opdId = $personnel->opd_id;
            }
        }

        // Auto-generate nama jika bukan manual
        $name = $request->input('name');
        if ($holderType === 'personnel' && $personnelId) {
            $personnel = Personnel::find($personnelId);
            $name = 'HP Personal - ' . ($personnel?->name ?? 'Personel');
        } elseif ($holderType === 'user' && $userId) {
            $targetUser = User::find($userId);
            $name = 'Global - ' . ($targetUser?->name ?? 'Admin');
        }

        $device = Device::create([
            'opd_id' => $opdId,
            'personnel_id' => $personnelId,
            'user_id' => $userId,
            'holder_name' => $holderName,
            'name' => $name,
            'license_key' => strtoupper(trim($request->input('license_key'))),
            'status' => $request->input('status', 'inactive'),
            'notes' => $request->input('notes'),
        ]);

        $device->load(['opd:id,name,singkatan', 'personnel:id,name,nik,foto', 'user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'message' => 'Perangkat baru berhasil ditambahkan.',
            'data' => $device,
        ], 201);
    }

    /**
     * 5. POST /api/v1/admin/devices/{id}
     * Memperbarui data perangkat.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $device = Device::find($id);

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data perangkat tidak ditemukan.',
            ], 404);
        }

        if (!$this->canManageDevice($user, $device)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengedit perangkat ini.',
            ], 403);
        }

        $isSuperAdmin = $this->isSuperAdmin($user);

        $validator = Validator::make($request->all(), [
            'opd_id' => $isSuperAdmin ? 'required|exists:opds,id' : 'nullable',
            'holder_type' => 'required|in:personnel,user,manual',
            'personnel_id' => 'required_if:holder_type,personnel|nullable|exists:personnels,id',
            'user_id' => 'required_if:holder_type,user|nullable|exists:users,id',
            'holder_name' => 'required_if:holder_type,manual|nullable|string|max:255',
            'name' => 'required_if:holder_type,manual|nullable|string|max:255',
            'license_key' => [
                'required',
                'string',
                'max:50',
                Rule::unique('devices', 'license_key')->ignore($id),
            ],
            'status' => 'required|in:active,inactive,suspended',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $holderType = $request->input('holder_type');
        $personnelId = $holderType === 'personnel' ? $request->input('personnel_id') : null;
        $userId = $holderType === 'user' ? $request->input('user_id') : null;
        $holderName = $holderType === 'manual' ? $request->input('holder_name') : null;

        // Cek duplikasi personel
        if ($holderType === 'personnel' && $personnelId) {
            $exists = Device::where('personnel_id', $personnelId)
                ->where('id', '!=', $id)
                ->exists();
            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Personel ini sudah memiliki perangkat terdaftar lain.',
                ], 422);
            }
        }

        $name = $request->input('name');
        if ($holderType === 'personnel' && $personnelId) {
            $personnel = Personnel::find($personnelId);
            $name = 'HP Personal - ' . ($personnel?->name ?? 'Personel');
        } elseif ($holderType === 'user' && $userId) {
            $targetUser = User::find($userId);
            $name = 'Global - ' . ($targetUser?->name ?? 'Admin');
        }

        $updateData = [
            'personnel_id' => $personnelId,
            'user_id' => $userId,
            'holder_name' => $holderName,
            'name' => $name,
            'license_key' => strtoupper(trim($request->input('license_key'))),
            'status' => $request->input('status'),
            'notes' => $request->input('notes'),
        ];

        if ($isSuperAdmin && $request->filled('opd_id')) {
            $updateData['opd_id'] = (int)$request->input('opd_id');
        }

        $device->update($updateData);
        $device->load(['opd:id,name,singkatan', 'personnel:id,name,nik,foto', 'user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'message' => 'Data perangkat berhasil diperbarui.',
            'data' => $device,
        ]);
    }

    /**
     * 6. POST /api/v1/admin/devices/{id}/toggle-status
     * Mengubah status perangkat antara active dan suspended.
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $device = Device::find($id);

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data perangkat tidak ditemukan.',
            ], 404);
        }

        if (!$this->canManageDevice($user, $device)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengubah status perangkat ini.',
            ], 403);
        }

        $newStatus = $device->status === 'active' ? 'suspended' : 'active';
        $device->update(['status' => $newStatus]);

        $msg = $newStatus === 'active' ? 'Perangkat berhasil diaktifkan.' : 'Perangkat berhasil disuspend (diblokir).';

        return response()->json([
            'status' => 'success',
            'message' => $msg,
            'data' => [
                'id' => $device->id,
                'status' => $newStatus,
            ],
        ]);
    }

    /**
     * 7. DELETE /api/v1/admin/devices/{id}
     * Menghapus perangkat dan mencabut semua access token mobile.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $device = Device::find($id);

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data perangkat tidak ditemukan.',
            ], 404);
        }

        if (!$this->canManageDevice($user, $device)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk menghapus perangkat ini.',
            ], 403);
        }

        $deviceName = $device->name;
        $device->delete(); // Model boot hook otomatis mencabut semua tokens

        return response()->json([
            'status' => 'success',
            'message' => "Perangkat \"{$deviceName}\" berhasil dihapus dari sistem.",
        ]);
    }
}
