<?php

namespace App\Http\Controllers\Api\V1\Personel;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\PersonnelFaceEmbedding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PersonnelFaceEnrollmentController extends Controller
{
    /**
     * Daftarkan atau perbarui 4 pose wajah 3D milik personel.
     */
    public function enroll(Request $request): JsonResponse
    {
        /** @var Personnel $personnel */
        $personnel = $request->attributes->get('personnel');

        $request->validate([
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
            'poses.*.foto' => 'nullable', // Bisa file upload atau dataUrl
            'poses.*.face_descriptor' => 'nullable', // 128-D descriptor untuk web
        ]);

        $savedPoses = [];
        $frontDescriptor192 = null;
        $frontPhotoPath = null;
        $frontDescriptorWeb = null;

        foreach ($request->poses as $index => $poseData) {
            $poseType = strtoupper($poseData['pose_type']);

            // Parse 192D
            $desc192 = is_array($poseData['face_descriptor_mobile'])
                ? $poseData['face_descriptor_mobile']
                : json_decode($poseData['face_descriptor_mobile'], true);
            $desc192Json = json_encode(array_map('floatval', $desc192));

            // Upload foto jika disertakan
            $photoPath = null;
            if ($request->hasFile("poses.$index.foto")) {
                $file = $request->file("poses.$index.foto");
                $filename = 'personnel_' . $personnel->id . '_' . strtolower($poseType) . '_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $photoPath = $file->storeAs('personnel-fotos/poses', $filename, 'public');
            } elseif (!empty($poseData['foto']) && is_string($poseData['foto']) && str_starts_with($poseData['foto'], 'data:image')) {
                // Base64 image
                $data = explode(',', $poseData['foto']);
                $decodedImg = base64_decode($data[1] ?? '');
                if ($decodedImg) {
                    $filename = 'personnel_' . $personnel->id . '_' . strtolower($poseType) . '_' . time() . '_' . Str::random(6) . '.jpg';
                    Storage::disk('public')->put('personnel-fotos/poses/' . $filename, $decodedImg);
                    $photoPath = 'personnel-fotos/poses/' . $filename;
                }
            }

            // Hapus foto lama jika ada berkas baru yang diunggah
            $existingFoto = $personnel->faceEmbeddings()->where('pose_type', $poseType)->value('foto');
            if ($photoPath && $existingFoto && $existingFoto !== $photoPath) {
                Storage::disk('public')->delete($existingFoto);
            }

            // Web descriptor (128-D) jika ada
            $descWeb = null;
            if (!empty($poseData['face_descriptor'])) {
                $descWebRaw = is_array($poseData['face_descriptor'])
                    ? $poseData['face_descriptor']
                    : json_decode($poseData['face_descriptor'], true);
                if (is_array($descWebRaw)) {
                    $descWeb = json_encode(array_map('floatval', $descWebRaw));
                }
            }

            // Update or Create pose record
            $embedding = PersonnelFaceEmbedding::updateOrCreate(
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

        // Sinkronisasi pose FRONT ke model utama Personnel
        if ($frontDescriptor192) {
            $personnel->face_descriptor_mobile = $frontDescriptor192;
            if (!$personnel->foto && $frontPhotoPath) {
                $personnel->foto = $frontPhotoPath;
            }
            if (!$personnel->face_descriptor && $frontDescriptorWeb) {
                $personnel->face_descriptor = $frontDescriptorWeb;
            }
        }

        // Set status verifikasi ke PENDING (belum bisa digunakan untuk absensi sebelum diverifikasi admin)
        $personnel->face_verification_status = 'PENDING';
        $personnel->face_verification_notes = null;
        $personnel->face_recognition = false;
        $personnel->save();

        // 1. Dispatch Real-Time Reverb WebSocket Event
        \App\Events\FaceEnrollmentSubmitted::dispatch(
            $personnel->id,
            $personnel->opd_id,
            $personnel->opd?->name ?? 'OPD TRC',
            $personnel->name,
            $personnel->nik,
            $personnel->foto ? asset('storage/' . $personnel->foto) : null
        );

        // 2. Kirim Push Notification FCM ke Admin yang berhak (Super Admin + Admin OPD ini)
        try {
            $targetAdminTokens = \App\Models\User::query()
                ->whereNotNull('fcm_token')
                ->where(function ($q) use ($personnel) {
                    $q->whereHas('roles', fn($r) => $r->whereIn('name', ['super-admin', 'dev']))
                      ->orWhereHas('permissions', fn($p) => $p->whereIn('name', ['edit-personel-all-opd', 'manajemen-personel']))
                      ->orWhere(function ($opdQ) use ($personnel) {
                          $opdQ->whereHas('roles', fn($r) => $r->where('name', 'admin-opd'))
                               ->whereHas('opds', fn($o) => $o->where('opds.id', $personnel->opd_id));
                      });
                })
                ->pluck('fcm_token')
                ->unique()
                ->filter()
                ->values()
                ->all();

            foreach ($targetAdminTokens as $token) {
                \App\Jobs\SendFcmNotificationJob::dispatch(
                    $token,
                    'Verifikasi Wajah Personel',
                    "{$personnel->name} ({$personnel->opd?->name}) meminta verifikasi perekaman wajah biometrik.",
                    [
                        'type' => 'face_verification',
                        'personnel_id' => (string)$personnel->id,
                        'opd_id' => (string)$personnel->opd_id,
                    ]
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal mengirim FCM admin untuk verifikasi wajah: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Perekaman wajah 3D berhasil disimpan ke server. Menunggu verifikasi admin.',
            'personnel_id' => $personnel->id,
            'saved_poses' => $savedPoses,
            'has_192d' => !empty($personnel->face_descriptor_mobile),
            'face_recognition_enabled' => false,
            'face_verification_status' => $personnel->face_verification_status,
        ]);
    }

    /**
     * Dapatkan master template wajah personel (192D & pose 3D) untuk pencocokan 1:1 on-device.
     */
    public function template(Request $request): JsonResponse
    {
        /** @var Personnel $personnel */
        $personnel = $request->attributes->get('personnel');

        $personnel->load('faceEmbeddings');

        $poses = [];
        foreach ($personnel->faceEmbeddings as $fe) {
            $poses[] = [
                'pose_type' => $fe->pose_type,
                'face_descriptor_mobile' => $fe->face_descriptor_mobile ? json_decode($fe->face_descriptor_mobile, true) : null,
                'foto' => $fe->foto ? asset('storage/' . $fe->foto) : null,
                'last_adapted_at' => $fe->last_adapted_at?->toIso8601String(),
            ];
        }

        return response()->json([
            'status' => 'success',
            'personnel_id' => $personnel->id,
            'name' => $personnel->name,
            'nik' => $personnel->nik,
            'master_descriptor_192' => $personnel->face_descriptor_mobile ? json_decode($personnel->face_descriptor_mobile, true) : null,
            'poses' => $poses,
            'total_poses' => count($poses),
        ]);
    }
}
