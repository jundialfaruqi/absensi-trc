<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Kantor;
use App\Models\Opd;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminKantorController extends Controller
{
    /**
     * Helper untuk memeriksa apakah user saat ini adalah Super Admin.
     */
    private function isSuperAdmin(Request $request): bool
    {
        $user = $request->attributes->get('auth_admin');
        if (!$user) {
            return false;
        }

        return in_array($user->role, ['SUPER_ADMIN', 'SUPERADMIN', 'super-admin'])
            || (method_exists($user, 'hasRole') && $user->hasRole('super-admin'));
    }

    /**
     * Helper untuk mendapatkan OPD ID user jika bukan Super Admin.
     */
    private function getUserOpdId(Request $request): ?int
    {
        $user = $request->attributes->get('auth_admin');
        if (!$user) {
            return null;
        }

        if (method_exists($user, 'opd') && $user->opd()) {
            return (int) $user->opd()->id;
        }

        return $user->opd_id ? (int) $user->opd_id : null;
    }

    /**
     * Mengambil daftar kantor terdaftar dengan paginasi.
     * GET /api/v1/admin/kantors
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $isSuperAdmin = $this->isSuperAdmin($request);
            $userOpdId = $this->getUserOpdId($request);
            $perPage = (int) $request->input('per_page', 10);
            if ($perPage <= 0 || $perPage > 50) {
                $perPage = 10;
            }

            $query = Kantor::with('opd')
                ->withCount('personnels');

            // Scoping OPD untuk non-Super Admin
            if (!$isSuperAdmin && $userOpdId) {
                $query->where('opd_id', $userOpdId);
            }

            $query->orderBy('name', 'asc');

            $paginated = $query->paginate($perPage);

            $data = collect($paginated->items())->map(function ($k) {
                return [
                    'id' => (int) $k->id,
                    'opd_id' => $k->opd_id ? (int) $k->opd_id : null,
                    'name' => (string) $k->name,
                    'alamat' => $k->alamat ? (string) $k->alamat : null,
                    'latitude' => (float) $k->latitude,
                    'longitude' => (float) $k->longitude,
                    'radius_meter' => (int) ($k->radius_meter ?? 100),
                    'is_active' => (bool) $k->is_active,
                    'personnels_count' => (int) ($k->personnels_count ?? 0),
                    'opd' => $k->opd ? [
                        'id' => (int) $k->opd->id,
                        'name' => (string) $k->opd->name,
                        'singkatan' => (string) ($k->opd->singkatan ?? $k->opd->name),
                    ] : null,
                ];
            });

            // Total count untuk stats
            $totalCount = $paginated->total();
            $activeCount = (clone $query)->where('is_active', true)->count();
            $inactiveCount = $totalCount - $activeCount;

            return response()->json([
                'success' => true,
                'message' => 'Daftar kantor berhasil diambil.',
                'data' => [
                    'kantors' => $data,
                    'stats' => [
                        'total' => $totalCount,
                        'active' => $activeCount,
                        'inactive' => $inactiveCount,
                    ],
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'last_page' => $paginated->lastPage(),
                        'per_page' => $paginated->perPage(),
                        'total' => $paginated->total(),
                        'has_more' => $paginated->hasMorePages(),
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data kantor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil opsi form untuk tambah/edit kantor (OPDs).
     * GET /api/v1/admin/kantors/form-options
     */
    public function formOptions(Request $request): JsonResponse
    {
        try {
            $isSuperAdmin = $this->isSuperAdmin($request);
            $userOpdId = $this->getUserOpdId($request);

            if ($isSuperAdmin) {
                $opds = Opd::orderBy('name')->get(['id', 'name', 'singkatan']);
            } else {
                $opds = Opd::where('id', $userOpdId)->get(['id', 'name', 'singkatan']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Opsi form kantor berhasil dimuat.',
                'data' => [
                    'is_super_admin' => $isSuperAdmin,
                    'default_opd_id' => $userOpdId,
                    'opds' => $opds->map(fn($o) => [
                        'id' => (int) $o->id,
                        'name' => (string) $o->name,
                        'singkatan' => (string) ($o->singkatan ?? $o->name),
                    ]),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat opsi form: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil detail kantor spesifik.
     * GET /api/v1/admin/kantors/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $isSuperAdmin = $this->isSuperAdmin($request);
            $userOpdId = $this->getUserOpdId($request);

            $kantor = Kantor::with('opd')
                ->withCount('personnels')
                ->findOrFail($id);

            // Cek otorisasi OPD jika bukan Super Admin
            if (!$isSuperAdmin && $userOpdId && $kantor->opd_id !== $userOpdId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke data kantor ini.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail kantor berhasil dimuat.',
                'data' => [
                    'id' => (int) $kantor->id,
                    'opd_id' => $kantor->opd_id ? (int) $kantor->opd_id : null,
                    'name' => (string) $kantor->name,
                    'alamat' => $kantor->alamat ? (string) $kantor->alamat : null,
                    'latitude' => (float) $kantor->latitude,
                    'longitude' => (float) $kantor->longitude,
                    'radius_meter' => (int) ($kantor->radius_meter ?? 100),
                    'is_active' => (bool) $kantor->is_active,
                    'personnels_count' => (int) ($kantor->personnels_count ?? 0),
                    'created_at' => $kantor->created_at ? $kantor->created_at->format('Y-m-d H:i:s') : null,
                    'opd' => $kantor->opd ? [
                        'id' => (int) $kantor->opd->id,
                        'name' => (string) $kantor->opd->name,
                        'singkatan' => (string) ($kantor->opd->singkatan ?? $kantor->opd->name),
                    ] : null,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data kantor tidak ditemukan.',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail kantor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Membuat data kantor baru.
     * POST /api/v1/admin/kantors
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $isSuperAdmin = $this->isSuperAdmin($request);
            $userOpdId = $this->getUserOpdId($request);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'opd_id' => 'required|exists:opds,id',
                'alamat' => 'nullable|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'radius_meter' => 'required|integer|min:50|max:10000',
                'is_active' => 'boolean',
            ]);

            // Scoping OPD
            if (!$isSuperAdmin && $userOpdId && (int) $validated['opd_id'] !== $userOpdId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat menambahkan kantor untuk OPD Anda sendiri.',
                ], 403);
            }

            $kantor = Kantor::create([
                'name' => trim($validated['name']),
                'opd_id' => (int) $validated['opd_id'],
                'alamat' => isset($validated['alamat']) ? trim($validated['alamat']) : null,
                'latitude' => (float) $validated['latitude'],
                'longitude' => (float) $validated['longitude'],
                'radius_meter' => (int) $validated['radius_meter'],
                'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Titik kantor berhasil ditambahkan.',
                'data' => [
                    'id' => (int) $kantor->id,
                    'name' => (string) $kantor->name,
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kantor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memperbarui data kantor yang sudah ada.
     * POST/PUT /api/v1/admin/kantors/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $isSuperAdmin = $this->isSuperAdmin($request);
            $userOpdId = $this->getUserOpdId($request);

            $kantor = Kantor::findOrFail($id);

            // Cek otorisasi OPD
            if (!$isSuperAdmin && $userOpdId && $kantor->opd_id !== $userOpdId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengubah kantor ini.',
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'opd_id' => 'required|exists:opds,id',
                'alamat' => 'nullable|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'radius_meter' => 'required|integer|min:50|max:10000',
                'is_active' => 'boolean',
            ]);

            if (!$isSuperAdmin && $userOpdId && (int) $validated['opd_id'] !== $userOpdId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak dapat memindahkan kantor ke OPD lain.',
                ], 403);
            }

            $kantor->update([
                'name' => trim($validated['name']),
                'opd_id' => (int) $validated['opd_id'],
                'alamat' => isset($validated['alamat']) ? trim($validated['alamat']) : null,
                'latitude' => (float) $validated['latitude'],
                'longitude' => (float) $validated['longitude'],
                'radius_meter' => (int) $validated['radius_meter'],
                'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : $kantor->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data kantor berhasil diperbarui.',
                'data' => [
                    'id' => (int) $kantor->id,
                    'name' => (string) $kantor->name,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data kantor tidak ditemukan.',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kantor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus kantor.
     * DELETE /api/v1/admin/kantors/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $isSuperAdmin = $this->isSuperAdmin($request);
            $userOpdId = $this->getUserOpdId($request);

            $kantor = Kantor::withCount('personnels')->findOrFail($id);

            if (!$isSuperAdmin && $userOpdId && $kantor->opd_id !== $userOpdId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus kantor ini.',
                ], 403);
            }

            $kantorName = $kantor->name;
            $kantor->delete();

            return response()->json([
                'success' => true,
                'message' => "Kantor '$kantorName' berhasil dihapus.",
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data kantor tidak ditemukan.',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kantor: ' . $e->getMessage(),
            ], 500);
        }
    }
}
