<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\PersonnelPhotoUpdated;
use App\Events\PersonnelVectorUpdated;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\PersonnelFaceEmbedding;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminPersonnelController extends Controller
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
            || $user->can('lihat-personel-all-opd')
            || $user->can('view-personel-all-opd')
            || $user->can('create-personel-all-opd')
            || $user->can('edit-personel-all-opd');
    }

    /**
     * Helper: Cek apakah user berhak mengelola data personel tertentu.
     */
    protected function canManagePersonnel(User $user, Personnel $personnel): bool
    {
        if ($this->isSuperAdmin($user) || $user->can('edit-personel-all-opd') || $user->can('delete-personel-all-opd')) {
            return true;
        }

        $userOpdId = $user->opds()->first()?->id;
        return !empty($userOpdId) && !empty($personnel->opd_id) && (int)$personnel->opd_id === (int)$userOpdId;
    }

    /**
     * 1. GET /api/v1/admin/personnels
     * Daftar personel dengan filter OPD, pencarian spesifik nama personel saja, dan pagination.
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

        $query = Personnel::query()
            ->with([
                'opd:id,name',
                'penugasan:id,name',
                'kantor:id,name',
                'faceEmbeddings:id,personnel_id,pose_type',
            ]);

        // Filter OPD
        if (!$isSuperAdmin && !empty($userOpdId)) {
            $query->where('opd_id', $userOpdId);
        } elseif ($request->filled('opd_id') && $request->input('opd_id') !== 'null' && $request->input('opd_id') !== '') {
            $query->where('opd_id', $request->input('opd_id'));
        }

        // Filter Penugasan opsional
        if ($request->filled('penugasan_id') && $request->input('penugasan_id') !== 'null' && $request->input('penugasan_id') !== '') {
            $query->where('penugasan_id', $request->input('penugasan_id'));
        }

        // Pencarian spesifik HANYA berdasarkan nama personel
        if ($request->filled('search')) {
            $search = trim((string)$request->input('search'));
            $query->where('name', 'like', "%{$search}%");
        }

        // Sorting & Pagination
        $query->orderBy('name', 'asc');
        $perPage = min(max((int)$request->input('per_page', 20), 1), 100);
        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map(function (Personnel $p) {
            $poses = $p->faceEmbeddings->pluck('pose_type')->toArray();
            return [
                'id' => $p->id,
                'name' => $p->name,
                'nik' => $p->nik,
                'opd_id' => $p->opd_id,
                'opd_name' => $p->opd?->name ?? '-',
                'penugasan_id' => $p->penugasan_id,
                'penugasan_name' => $p->penugasan?->name ?? '-',
                'kantor_id' => $p->kantor_id,
                'kantor_name' => $p->kantor?->name ?? '-',
                'nomor_hp' => $p->nomor_hp,
                'email' => $p->email,
                'foto_url' => $p->foto ? asset('storage/' . $p->foto) : null,
                'attendance_type' => $p->attendance_type ?? 'SCHEDULED',
                'wajib_absen_di_lokasi' => (bool)$p->wajib_absen_di_lokasi,
                'face_recognition' => (bool)$p->face_recognition,
                'has_face_data' => !empty($p->face_descriptor_mobile) || count($poses) >= 4,
                'total_3d_poses' => count($poses),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * 2. GET /api/v1/admin/personnels/form-options
     * Daftar pilihan dropdown (OPD, Penugasan, Kantor) yang dapat diakses oleh admin.
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

        // 1. OPDs
        if ($isSuperAdmin) {
            $opds = Opd::query()->orderBy('name', 'asc')->get(['id', 'name']);
        } else {
            $opds = !empty($userOpdId)
                ? Opd::query()->where('id', $userOpdId)->get(['id', 'name'])
                : Opd::query()->orderBy('name', 'asc')->get(['id', 'name']);
        }

        // 2. Penugasans
        $penugasans = Penugasan::query()->orderBy('name', 'asc')->get(['id', 'name']);

        // 3. Kantors
        $kantorsQuery = Kantor::query()->orderBy('name', 'asc');
        if (!$isSuperAdmin && !empty($userOpdId)) {
            $kantorsQuery->where('opd_id', $userOpdId);
        }
        $kantors = $kantorsQuery->get(['id', 'name', 'opd_id', 'latitude', 'longitude', 'radius_meter']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'opds' => $opds,
                'penugasans' => $penugasans,
                'kantors' => $kantors,
                'is_super_admin' => $isSuperAdmin,
                'default_opd_id' => $isSuperAdmin ? null : $userOpdId,
            ],
        ]);
    }

    /**
     * 3. GET /api/v1/admin/personnels/{id}
     * Detail personel lengkap untuk Pop-up Dialog & Form Edit.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $personnel = Personnel::with(['opd', 'penugasan', 'kantor', 'faceEmbeddings', 'devices'])->find($id);

        if (!$personnel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data personel tidak ditemukan.',
            ], 404);
        }

        if (!$this->canManagePersonnel($user, $personnel)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk melihat personel dari OPD lain.',
            ], 403);
        }

        $existingPoses = $personnel->faceEmbeddings->pluck('pose_type')->toArray();
        $totalAdaptations = (int)$personnel->faceEmbeddings->sum('adaptation_count');
        $hasAdaptiveBiometrics = $personnel->faceEmbeddings()->whereNotNull('adaptive_descriptor_mobile')->exists();
        $device = $personnel->devices->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $personnel->id,
                'name' => $personnel->name,
                'nik' => $personnel->nik,
                'opd_id' => $personnel->opd_id,
                'opd_name' => $personnel->opd?->name ?? '-',
                'penugasan_id' => $personnel->penugasan_id,
                'penugasan_name' => $personnel->penugasan?->name ?? '-',
                'kantor_id' => $personnel->kantor_id,
                'kantor_name' => $personnel->kantor?->name ?? '-',
                'nomor_hp' => $personnel->nomor_hp ?? '',
                'email' => $personnel->email,
                'pin' => $personnel->pin ?? '',
                'attendance_type' => $personnel->attendance_type ?? 'SCHEDULED',
                'wajib_absen_di_lokasi' => (bool)$personnel->wajib_absen_di_lokasi,
                'face_recognition' => (bool)$personnel->face_recognition,
                'foto_url' => $personnel->foto ? asset('storage/' . $personnel->foto) : null,
                'has_face_data' => !empty($personnel->face_descriptor_mobile) || count($existingPoses) >= 4,
                'existing_3d_poses' => $existingPoses,
                'total_3d_poses' => count($existingPoses),
                'total_adaptations' => $totalAdaptations,
                'has_adaptive_biometrics' => $hasAdaptiveBiometrics,
                'has_personal_device' => !empty($device),
                'device_name' => $device?->name ?? '',
                'license_key' => $device?->license_key ?? '',
                'device_status' => $device?->status ?? 'none',
                'created_at' => $personnel->created_at?->toIso8601String(),
                'updated_at' => $personnel->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * 4. POST /api/v1/admin/personnels
     * Tambah personel baru dengan PIN unik, email otomatis, dan opsi pembuatan lisensi perangkat.
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
            'name' => 'required|string|max:255',
            'nik' => 'required|string|max:16|unique:personnels,nik',
            'opd_id' => 'required|exists:opds,id',
            'penugasan_id' => 'required|exists:penugasans,id',
            'nomor_hp' => 'nullable|string|max:15',
            'pin' => 'nullable|string|size:6|unique:personnels,pin',
            'kantor_id' => 'nullable|exists:kantors,id',
            'attendance_type' => 'required|in:SCHEDULED,FLEXIBLE',
            'wajib_absen_di_lokasi' => 'boolean',
            'auto_create_device' => 'boolean',
            'foto' => 'nullable|image|max:4096',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nik.required' => 'NIK / NIP wajib diisi.',
            'nik.unique' => 'NIK / NIP sudah terdaftar di sistem.',
            'opd_id.required' => 'OPD wajib dipilih.',
            'penugasan_id.required' => 'Penugasan wajib dipilih.',
            'pin.unique' => 'PIN sudah digunakan personel lain.',
            'foto.image' => 'Berkas foto harus berupa gambar valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $opdId = (int)$request->input('opd_id');
        if (!$isSuperAdmin && $opdId !== (int)$userOpdId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak diizinkan membuat personel untuk OPD selain OPD Anda sendiri.',
            ], 403);
        }

        // Generate PIN jika kosong
        $pin = $request->input('pin');
        if (empty($pin)) {
            $pin = $this->generateUniquePin();
        }

        // Generate Email Unik
        $name = $request->input('name');
        $baseEmail = strtolower(str_replace(' ', '', $name));
        $email = $baseEmail . '@trc.com';
        $counter = 1;
        while (Personnel::query()->where('email', $email)->exists()) {
            $email = $baseEmail . $counter . '@trc.com';
            $counter++;
        }

        // Generate Default Password
        $penugasan = Penugasan::find($request->input('penugasan_id'));
        $suffix = $penugasan ? strtolower(str_replace(' ', '', $penugasan->name)) : 'dev';
        $password = Hash::make('admintrc112_' . $suffix);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('personnel-fotos', 'public');
        }

        $personnel = Personnel::create([
            'name' => $name,
            'nik' => $request->input('nik'),
            'opd_id' => $opdId,
            'penugasan_id' => $request->input('penugasan_id'),
            'kantor_id' => $request->input('kantor_id') ?: null,
            'nomor_hp' => $request->input('nomor_hp') ?: null,
            'email' => $email,
            'password' => $password,
            'pin' => $pin,
            'attendance_type' => $request->input('attendance_type', 'SCHEDULED'),
            'wajib_absen_di_lokasi' => $request->boolean('wajib_absen_di_lokasi', false),
            'face_recognition' => false,
            'foto' => $fotoPath,
        ]);

        if ($fotoPath) {
            PersonnelPhotoUpdated::dispatch(
                $personnel->id,
                $personnel->opd_id,
                $personnel->name,
                asset('storage/' . $fotoPath)
            );
        }

        $licenseKey = null;
        if ($request->boolean('auto_create_device', false)) {
            $licenseKey = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
            Device::create([
                'opd_id' => $personnel->opd_id,
                'personnel_id' => $personnel->id,
                'name' => 'HP Personal - ' . $personnel->name,
                'license_key' => $licenseKey,
                'status' => 'inactive',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Personel baru berhasil ditambahkan.' . ($licenseKey ? " | License Key: {$licenseKey}" : ''),
            'data' => [
                'id' => $personnel->id,
                'name' => $personnel->name,
                'nik' => $personnel->nik,
                'email' => $personnel->email,
                'pin' => $personnel->pin,
                'license_key' => $licenseKey,
                'foto_url' => $fotoPath ? asset('storage/' . $fotoPath) : null,
            ],
        ], 201);
    }

    /**
     * 5. POST /api/v1/admin/personnels/{id}
     * Update data personel.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $personnel = Personnel::find($id);

        if (!$personnel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data personel tidak ditemukan.',
            ], 404);
        }

        if (!$this->canManagePersonnel($user, $personnel)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengedit personel ini.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nik' => [
                'required',
                'string',
                'max:16',
                Rule::unique('personnels', 'nik')->ignore($id),
            ],
            'opd_id' => 'required|exists:opds,id',
            'penugasan_id' => 'required|exists:penugasans,id',
            'nomor_hp' => 'nullable|string|max:15',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('personnels', 'email')->ignore($id),
            ],
            'pin' => [
                'required',
                'string',
                'size:6',
                Rule::unique('personnels', 'pin')->ignore($id),
            ],
            'password' => 'nullable|string|min:6',
            'kantor_id' => 'nullable|exists:kantors,id',
            'attendance_type' => 'required|in:SCHEDULED,FLEXIBLE',
            'wajib_absen_di_lokasi' => 'boolean',
            'auto_create_device' => 'boolean',
            'foto' => 'nullable|image|max:4096',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nik.required' => 'NIK / NIP wajib diisi.',
            'nik.unique' => 'NIK / NIP sudah terdaftar di sistem.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'pin.required' => 'PIN wajib diisi.',
            'pin.size' => 'PIN harus tepat 6 digit.',
            'pin.unique' => 'PIN sudah digunakan personel lain.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $isSuperAdmin = $this->isSuperAdmin($user);
        $opdId = (int)$request->input('opd_id');
        $userOpdId = $user->opds()->first()?->id;

        if (!$isSuperAdmin && $opdId !== (int)$userOpdId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak diizinkan memindahkan personel ke OPD lain.',
            ], 403);
        }

        $updateData = [
            'name' => $request->input('name'),
            'nik' => $request->input('nik'),
            'opd_id' => $opdId,
            'penugasan_id' => $request->input('penugasan_id'),
            'kantor_id' => $request->input('kantor_id') ?: null,
            'nomor_hp' => $request->input('nomor_hp') ?: null,
            'email' => $request->input('email'),
            'pin' => $request->input('pin'),
            'attendance_type' => $request->input('attendance_type', 'SCHEDULED'),
            'wajib_absen_di_lokasi' => $request->boolean('wajib_absen_di_lokasi', false),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->input('password'));
        }

        if ($request->hasFile('foto')) {
            $newPath = $request->file('foto')->store('personnel-fotos', 'public');
            if ($personnel->foto && Storage::disk('public')->exists($personnel->foto)) {
                Storage::disk('public')->delete($personnel->foto);
            }
            $updateData['foto'] = $newPath;
            $updateData['face_descriptor_mobile'] = null; // Reset deskriptor agar sinkron dengan foto baru
        }

        $personnel->update($updateData);

        if (isset($updateData['foto'])) {
            PersonnelPhotoUpdated::dispatch(
                $personnel->id,
                $personnel->opd_id,
                $personnel->name,
                asset('storage/' . $updateData['foto'])
            );
        }

        $licenseMsg = '';
        if ($request->boolean('auto_create_device', false) && !$personnel->devices()->exists()) {
            $licenseKey = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
            Device::create([
                'opd_id' => $personnel->opd_id,
                'personnel_id' => $personnel->id,
                'name' => 'HP Personal - ' . $personnel->name,
                'license_key' => $licenseKey,
                'status' => 'inactive',
            ]);
            $licenseMsg = " | License Key: {$licenseKey}";
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data personel berhasil diperbarui.' . $licenseMsg,
            'data' => [
                'id' => $personnel->id,
                'name' => $personnel->name,
                'nik' => $personnel->nik,
                'email' => $personnel->email,
                'pin' => $personnel->pin,
                'foto_url' => $personnel->foto ? asset('storage/' . $personnel->foto) : null,
            ],
        ]);
    }

    /**
     * 6. DELETE /api/v1/admin/personnels/{id}
     * Hapus personel dan seluruh relasi berkas / biometrik.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $personnel = Personnel::find($id);

        if (!$personnel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data personel tidak ditemukan.',
            ], 404);
        }

        if (!$this->canManagePersonnel($user, $personnel)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk menghapus personel ini.',
            ], 403);
        }

        $opdId = $personnel->opd_id;
        $personnelId = $personnel->id;

        // Model deleting hook otomatis membersihkan storage foto & pose & device
        $personnel->delete();

        PersonnelVectorUpdated::dispatch($personnelId, $opdId, 'deleted');

        return response()->json([
            'status' => 'success',
            'message' => 'Data personel berhasil dihapus dari sistem.',
        ]);
    }

    /**
     * 7. DELETE /api/v1/admin/personnels/{id}/face-data
     * Hapus seluruh data biometrik wajah (128D, 192D, 3D poses) dan foto personel.
     */
    public function deleteFaceData(Request $request, int $id): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $personnel = Personnel::with('faceEmbeddings')->find($id);

        if (!$personnel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data personel tidak ditemukan.',
            ], 404);
        }

        if (!$this->canManagePersonnel($user, $personnel)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk menghapus data biometrik personel ini.',
            ], 403);
        }

        // 1. Hapus seluruh berkas foto pose 3D dari storage
        foreach ($personnel->faceEmbeddings as $embedding) {
            if ($embedding->foto && Storage::disk('public')->exists($embedding->foto)) {
                Storage::disk('public')->delete($embedding->foto);
            }
        }
        $personnel->faceEmbeddings()->delete();

        // 2. Hapus berkas foto utama jika ada
        if ($personnel->foto && Storage::disk('public')->exists($personnel->foto)) {
            Storage::disk('public')->delete($personnel->foto);
        }

        // 3. Reset kolom biometrik di database
        $personnel->update([
            'foto' => null,
            'face_descriptor' => null,
            'face_descriptor_mobile' => null,
            'face_recognition' => false,
        ]);

        // 4. Broadcast sinkronisasi realtime
        PersonnelVectorUpdated::dispatch($personnel->id, $personnel->opd_id, 'deleted');

        return response()->json([
            'status' => 'success',
            'message' => 'Seluruh data biometrik wajah (128D, 192D, 3D poses) dan foto personel berhasil dihapus.',
        ]);
    }

    /**
     * 8. POST /api/v1/admin/personnels/{id}/face-enroll
     * Perekaman wajah 3D multi-angle & master template 192D untuk personel.
     */
    public function enrollFace(Request $request, int $id): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $personnel = Personnel::find($id);

        if (!$personnel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data personel tidak ditemukan.',
            ], 404);
        }

        if (!$this->canManagePersonnel($user, $personnel)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk merekam wajah personel ini.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'poses' => 'required|array|min:1',
            'poses.*.pose_type' => 'required|string|in:FRONT,RIGHT,LEFT,UP',
            'poses.*.face_descriptor_mobile' => [
                'required',
                function ($attribute, $value, $fail) {
                    $decoded = is_array($value) ? $value : json_decode($value, true);
                    if (!is_array($decoded) || count($decoded) !== 192) {
                        return $fail('face_descriptor_mobile harus berupa array tepat 192 elemen numerik float.');
                    }
                    foreach ($decoded as $num) {
                        if (!is_numeric($num)) {
                            return $fail('Setiap elemen dalam face_descriptor_mobile harus numerik.');
                        }
                    }
                },
            ],
            'poses.*.foto' => 'nullable',
            'poses.*.face_descriptor' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $savedPoses = [];
        $frontDescriptor192 = null;
        $frontPhotoPath = null;
        $frontDescriptorWeb = null;

        foreach ($request->poses as $index => $poseData) {
            $poseType = strtoupper($poseData['pose_type']);

            $desc192 = is_array($poseData['face_descriptor_mobile'])
                ? $poseData['face_descriptor_mobile']
                : json_decode($poseData['face_descriptor_mobile'], true);
            $desc192Json = json_encode(array_map('floatval', $desc192));

            $photoPath = null;
            if ($request->hasFile("poses.$index.foto")) {
                $file = $request->file("poses.$index.foto");
                $filename = 'personnel_' . $personnel->id . '_' . strtolower($poseType) . '_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $photoPath = $file->storeAs('personnel-fotos/poses', $filename, 'public');
            } elseif (!empty($poseData['foto']) && is_string($poseData['foto']) && str_starts_with($poseData['foto'], 'data:image')) {
                $data = explode(',', $poseData['foto']);
                $decodedImg = base64_decode($data[1] ?? '');
                if ($decodedImg) {
                    $filename = 'personnel_' . $personnel->id . '_' . strtolower($poseType) . '_' . time() . '_' . Str::random(6) . '.jpg';
                    Storage::disk('public')->put('personnel-fotos/poses/' . $filename, $decodedImg);
                    $photoPath = 'personnel-fotos/poses/' . $filename;
                }
            }

            $existingFoto = $personnel->faceEmbeddings()->where('pose_type', $poseType)->value('foto');
            if ($photoPath && $existingFoto && $existingFoto !== $photoPath) {
                Storage::disk('public')->delete($existingFoto);
            }

            $descWeb = null;
            if (!empty($poseData['face_descriptor'])) {
                $descWebRaw = is_array($poseData['face_descriptor'])
                    ? $poseData['face_descriptor']
                    : json_decode($poseData['face_descriptor'], true);
                if (is_array($descWebRaw)) {
                    $descWeb = json_encode(array_map('floatval', $descWebRaw));
                }
            }

            PersonnelFaceEmbedding::updateOrCreate(
                [
                    'personnel_id' => $personnel->id,
                    'pose_type' => $poseType,
                ],
                [
                    'face_descriptor_mobile' => $desc192Json,
                    'face_descriptor' => $descWeb,
                    'foto' => $photoPath ?: $existingFoto,
                    'last_adapted_at' => now(),
                ]
            );

            $savedPoses[] = $poseType;

            if ($poseType === 'FRONT') {
                $frontDescriptor192 = $desc192Json;
                $frontPhotoPath = $photoPath;
                $frontDescriptorWeb = $descWeb;
            }
        }

        if ($frontDescriptor192) {
            $personnel->face_descriptor_mobile = $frontDescriptor192;
            if ($frontPhotoPath) {
                $personnel->foto = $frontPhotoPath;
            }
            if ($frontDescriptorWeb) {
                $personnel->face_descriptor = $frontDescriptorWeb;
            }
            $personnel->face_recognition = true;
            $personnel->save();

            PersonnelVectorUpdated::dispatch(
                $personnel->id,
                $personnel->opd_id,
                'ready'
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Perekaman wajah 3D personel berhasil disimpan ke server.',
            'personnel_id' => $personnel->id,
            'saved_poses' => $savedPoses,
            'has_192d' => !empty($personnel->face_descriptor_mobile),
            'face_recognition_enabled' => (bool)$personnel->face_recognition,
        ]);
    }

    /**
     * Helper: Menghasilkan 6-digit PIN unik.
     */
    private function generateUniquePin(): string
    {
        do {
            $pin = sprintf('%06d', mt_rand(1, 999999));
        } while (Personnel::query()->where('pin', $pin)->exists());

        return $pin;
    }
}
