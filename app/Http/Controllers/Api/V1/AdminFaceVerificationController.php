<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\FaceVerificationProcessed;
use App\Events\PersonnelVectorUpdated;
use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminFaceVerificationController extends Controller
{
    /**
     * Otorisasi: Role kordinator dan admin-absen tidak memiliki hak akses ke verifikasi biometrika wajah.
     */
    private function authorizeVerificationRole(Request $request): ?JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if ($user && ($user->hasRole('kordinator') || $user->hasRole('admin-absen'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Role ini tidak memiliki hak akses ke verifikasi biometrika wajah.',
            ], 403);
        }

        return null;
    }

    /**
     * Cek apakah user memiliki hak akses menyeluruh (super-admin, dev, atau edit-personel-all-opd).
     */
    private function isSuperAdminUser(User $user): bool
    {
        return $user->hasRole('super-admin')
            || $user->hasRole('dev')
            || $user->can('edit-personel-all-opd')
            || $user->can('lihat-personel-all-opd');
    }

    /**
     * Daftar antrean verifikasi biometrika wajah personel (status = PENDING).
     * Terfilter otomatis berdasarkan OPD (kecuali super-admin).
     */
    public function index(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeVerificationRole($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $isSuperAdmin = $this->isSuperAdminUser($user);
        $userOpdId = $user->opd()?->id;

        $status = $request->query('status', 'PENDING');

        $query = Personnel::query()
            ->with(['opd', 'kantor', 'penugasan', 'faceEmbeddings'])
            ->when($status !== 'ALL', function ($q) use ($status) {
                $q->where('face_verification_status', $status);
            })
            ->when(!$isSuperAdmin, function ($q) use ($userOpdId) {
                if ($userOpdId) {
                    $q->where('opd_id', $userOpdId);
                }
            })
            ->when($isSuperAdmin && $request->filled('opd_id'), function ($q) use ($request) {
                $q->where('opd_id', $request->opd_id);
            })
            ->orderBy('updated_at', 'desc');

        $personnels = $query->paginate($request->query('per_page', 20));

        $data = $personnels->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'nik' => $p->nik,
                'foto' => $p->foto ? asset('storage/' . $p->foto) : null,
                'opd_id' => $p->opd_id,
                'opd_name' => $p->opd?->name ?? 'OPD TRC',
                'kantor_name' => $p->kantor?->name ?? $p->kantor?->nama_kantor ?? '-',
                'penugasan_name' => $p->penugasan?->name ?? '-',
                'regu' => $p->regu ?? '-',
                'face_verification_status' => $p->face_verification_status ?? 'UNREGISTERED',
                'face_verification_notes' => $p->face_verification_notes,
                'total_poses' => $p->faceEmbeddings->count(),
                'updated_at' => $p->updated_at?->toISOString(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar antrean verifikasi wajah berhasil diambil.',
            'data' => $data,
            'meta' => [
                'current_page' => $personnels->currentPage(),
                'last_page' => $personnels->lastPage(),
                'per_page' => $personnels->perPage(),
                'total' => $personnels->total(),
            ],
        ]);
    }

    /**
     * Detail personel dan 4 pose hasil rekaman biometrika untuk verifikasi.
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        if ($authError = $this->authorizeVerificationRole($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $isSuperAdmin = $this->isSuperAdminUser($user);
        $userOpdId = $user->opd()?->id;

        $personnel = Personnel::with(['opd', 'kantor', 'penugasan', 'faceEmbeddings', 'verifier'])
            ->find($id);

        if (!$personnel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data personel tidak ditemukan.',
            ], 404);
        }

        // Penyekatan Hak Akses OPD
        if (!$isSuperAdmin && $userOpdId && (int)$personnel->opd_id !== (int)$userOpdId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Personel ini bukan bagian dari OPD Anda.',
            ], 403);
        }

        $poses = [];
        foreach (['FRONT', 'RIGHT', 'LEFT', 'UP'] as $type) {
            $embedding = $personnel->faceEmbeddings->firstWhere('pose_type', $type);
            $poses[] = [
                'pose_type' => $type,
                'label' => match ($type) {
                    'FRONT' => 'Wajah Depan',
                    'RIGHT' => 'Toleh Kanan',
                    'LEFT' => 'Toleh Kiri',
                    'UP' => 'Tengadah Atas',
                    default => $type,
                },
                'foto' => $embedding && $embedding->foto ? asset('storage/' . $embedding->foto) : null,
                'has_embedding' => !empty($embedding?->face_descriptor_mobile),
                'last_adapted_at' => $embedding?->last_adapted_at?->toISOString(),
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail verifikasi wajah personel berhasil diambil.',
            'data' => [
                'personnel' => [
                    'id' => $personnel->id,
                    'name' => $personnel->name,
                    'nik' => $personnel->nik,
                    'nomor_hp' => $personnel->nomor_hp ?? '-',
                    'foto_master' => $personnel->foto ? asset('storage/' . $personnel->foto) : null,
                    'opd_id' => $personnel->opd_id,
                    'opd_name' => $personnel->opd?->name ?? 'OPD TRC',
                    'kantor_name' => $personnel->kantor?->name ?? $personnel->kantor?->nama_kantor ?? '-',
                    'penugasan_name' => $personnel->penugasan?->name ?? '-',
                    'regu' => $personnel->regu ?? '-',
                    'face_verification_status' => $personnel->face_verification_status ?? 'UNREGISTERED',
                    'face_verification_notes' => $personnel->face_verification_notes,
                    'face_verified_at' => $personnel->face_verified_at?->toISOString(),
                    'verifier_name' => $personnel->verifier?->name,
                    'has_192d' => !empty($personnel->face_descriptor_mobile),
                ],
                'poses' => $poses,
            ],
        ]);
    }

    /**
     * Menyetujui verifikasi perekaman biometrika wajah personel.
     */
    public function approve(Request $request, int|string $id): JsonResponse
    {
        if ($authError = $this->authorizeVerificationRole($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $isSuperAdmin = $this->isSuperAdminUser($user);
        $userOpdId = $user->opd()?->id;

        $personnel = Personnel::find($id);

        if (!$personnel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data personel tidak ditemukan.',
            ], 404);
        }

        // Penyekatan Hak Akses OPD
        if (!$isSuperAdmin && $userOpdId && (int)$personnel->opd_id !== (int)$userOpdId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Anda tidak berhak memverifikasi personel di luar OPD Anda.',
            ], 403);
        }

        $personnel->face_verification_status = 'APPROVED';
        $personnel->face_verification_notes = null;
        $personnel->face_recognition = true;
        $personnel->face_verified_at = now();
        $personnel->face_verified_by = $user->id;
        $personnel->save();

        // Broadcast Real-Time Reverb
        FaceVerificationProcessed::dispatch($personnel->id, 'APPROVED', null, $personnel->face_verified_at->toISOString());
        PersonnelVectorUpdated::dispatch($personnel->id, $personnel->opd_id, 'ready');

        // Kirim Push Notifikasi FCM ke HP Personel
        if (!empty($personnel->fcm_token)) {
            \App\Jobs\SendFcmNotificationJob::dispatch(
                $personnel->fcm_token,
                'Verifikasi Wajah Disetujui',
                'Selamat! Perekaman wajah biometrik Anda telah disetujui. Anda sekarang dapat melakukan absensi.',
                [
                    'type' => 'face_verification_processed',
                    'status' => 'APPROVED',
                    'personnel_id' => (string)$personnel->id,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => "Verifikasi wajah biometrik {$personnel->name} berhasil disetujui. Personel sekarang dapat melakukan absensi.",
            'data' => [
                'personnel_id' => $personnel->id,
                'status' => 'APPROVED',
                'verified_at' => $personnel->face_verified_at->toISOString(),
                'verified_by' => $user->name,
            ],
        ]);
    }

    /**
     * Menolak verifikasi perekaman biometrika wajah personel beserta alasan penolakan.
     */
    public function reject(Request $request, int|string $id): JsonResponse
    {
        if ($authError = $this->authorizeVerificationRole($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $isSuperAdmin = $this->isSuperAdminUser($user);
        $userOpdId = $user->opd()?->id;

        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|min:3|max:500',
        ], [
            'notes.required' => 'Alasan penolakan wajib diisi.',
            'notes.min' => 'Alasan penolakan minimal 3 karakter.',
            'notes.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $personnel = Personnel::find($id);

        if (!$personnel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data personel tidak ditemukan.',
            ], 404);
        }

        // Penyekatan Hak Akses OPD
        if (!$isSuperAdmin && $userOpdId && (int)$personnel->opd_id !== (int)$userOpdId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Anda tidak berhak menolak personel di luar OPD Anda.',
            ], 403);
        }

        $notes = trim($request->input('notes'));

        $personnel->face_verification_status = 'REJECTED';
        $personnel->face_verification_notes = $notes;
        $personnel->face_recognition = false;
        $personnel->face_verified_at = now();
        $personnel->face_verified_by = $user->id;
        $personnel->save();

        // Broadcast Real-Time Reverb
        FaceVerificationProcessed::dispatch($personnel->id, 'REJECTED', $notes, $personnel->face_verified_at->toISOString());

        // Kirim Push Notifikasi FCM ke HP Personel
        if (!empty($personnel->fcm_token)) {
            \App\Jobs\SendFcmNotificationJob::dispatch(
                $personnel->fcm_token,
                'Verifikasi Wajah Ditolak',
                "Perekaman wajah Anda ditolak oleh admin. Alasan: {$notes}",
                [
                    'type' => 'face_verification_processed',
                    'status' => 'REJECTED',
                    'notes' => $notes,
                    'personnel_id' => (string)$personnel->id,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => "Verifikasi wajah {$personnel->name} ditolak. Personel diminta untuk mengulangi perekaman wajah.",
            'data' => [
                'personnel_id' => $personnel->id,
                'status' => 'REJECTED',
                'notes' => $notes,
                'verified_at' => $personnel->face_verified_at->toISOString(),
                'verified_by' => $user->name,
            ],
        ]);
    }
}
