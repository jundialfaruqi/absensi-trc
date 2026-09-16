<?php

namespace App\Services;

use App\Events\PersonnelVectorUpdated;
use App\Models\Personnel;
use App\Models\PersonnelFaceEmbedding;
use App\Models\PersonnelFaceLearningLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AdaptiveFaceLearningService
{
    // Threshold & Parameter Configuration
    public const MIN_CONFIDENCE_GATE = 0.85; // 85.0% confidence gate
    public const DRIFT_GUARD_THRESHOLD = 0.70; // Minimum similarity to Master Anchor
    public const DEFAULT_LEARNING_RATE_ALPHA = 0.05; // 5% weight on new capture
    public const MAX_ALLOWED_YAW_DEG = 15.0;
    public const MAX_ALLOWED_PITCH_DEG = 15.0;
    public const MAX_ALLOWED_ROLL_DEG = 12.0;

    /**
     * Mencoba melakukan adaptasi biometrik berkelanjutan (Pilar 4) melalui 4 Lapis Pintu Seleksi.
     *
     * @param Personnel $personnel
     * @param array|string $capturedDescriptor 192-D float array atau JSON string
     * @param float $confidenceScore Kemiripan saat absensi (0.0 - 1.0 atau 0 - 100)
     * @param string $poseType Default 'FRONT'
     * @param int|null $absensiId ID transaksi absensi
     * @param string|null $deviceInfo Nama perangkat / OS
     * @param array|null $eulerAngles Array euler ['yaw' => float, 'pitch' => float, 'roll' => float]
     * @return array [
     *     'adapted' => bool,
     *     'reason' => string,
     *     'confidence_score' => float,
     *     'drift_to_master' => ?float,
     *     'adaptation_count' => int
     * ]
     */
    public function attemptAdaptation(
        Personnel $personnel,
        array|string $capturedDescriptor,
        float $confidenceScore,
        string $poseType = 'FRONT',
        ?int $absensiId = null,
        ?string $deviceInfo = null,
        ?array $eulerAngles = null
    ): array {
        // Normalisasi confidence score (jika dikirim dalam skala 0 - 100)
        $normalizedConfidence = $confidenceScore > 1.0 ? ($confidenceScore / 100.0) : $confidenceScore;

        // --- GATE 1: Confidence Gate (Wajib >= 85.0%) ---
        if ($normalizedConfidence < self::MIN_CONFIDENCE_GATE) {
            return [
                'adapted' => false,
                'reason' => 'Gate 1 (Confidence Gate) ditolak: Kemiripan ' . round($normalizedConfidence * 100, 1) . '% di bawah syarat minimal ' . (self::MIN_CONFIDENCE_GATE * 100) . '%.',
                'confidence_score' => $normalizedConfidence,
                'drift_to_master' => null,
                'adaptation_count' => 0,
            ];
        }

        // Cari atau dapatkan master anchor
        $embedding = $personnel->faceEmbeddings()->where('pose_type', $poseType)->first();

        // Fallback untuk FRONT: jika belum ada di tabel faceEmbeddings, periksa kolom utama di personnels
        $masterDescriptorStr = $embedding?->face_descriptor_mobile;
        if (!$masterDescriptorStr && $poseType === 'FRONT') {
            $masterDescriptorStr = $personnel->face_descriptor_mobile;
        }

        if (!$masterDescriptorStr) {
            return [
                'adapted' => false,
                'reason' => 'Gate 4 ditolak: Master Anchor biometrik untuk pose ' . $poseType . ' belum terdaftar.',
                'confidence_score' => $normalizedConfidence,
                'drift_to_master' => null,
                'adaptation_count' => 0,
            ];
        }

        // --- GATE 2: Rate Limiting (Maksimal 1x per hari per personel per pose) ---
        if ($embedding && $embedding->last_adapted_at && $embedding->last_adapted_at->isToday()) {
            return [
                'adapted' => false,
                'reason' => 'Gate 2 (Rate Limiting) ditolak: Adaptasi biometrik untuk pose ' . $poseType . ' sudah dilakukan hari ini (' . $embedding->last_adapted_at->format('H:i:s') . ').',
                'confidence_score' => $normalizedConfidence,
                'drift_to_master' => null,
                'adaptation_count' => $embedding->adaptation_count ?? 0,
            ];
        }

        // --- GATE 3: Geometry Gate (Wajah tegak lurus dan stabil) ---
        if ($eulerAngles !== null && !empty($eulerAngles)) {
            $yaw = abs((float)($eulerAngles['yaw'] ?? 0.0));
            $pitch = abs((float)($eulerAngles['pitch'] ?? 0.0));
            $roll = abs((float)($eulerAngles['roll'] ?? 0.0));

            if ($yaw > self::MAX_ALLOWED_YAW_DEG || $pitch > self::MAX_ALLOWED_PITCH_DEG || $roll > self::MAX_ALLOWED_ROLL_DEG) {
                return [
                    'adapted' => false,
                    'reason' => "Gate 3 (Geometry Gate) ditolak: Kemiringan wajah melebihi batas toleransi (Yaw: {$yaw}°, Pitch: {$pitch}°, Roll: {$roll}°).",
                    'confidence_score' => $normalizedConfidence,
                    'drift_to_master' => null,
                    'adaptation_count' => $embedding?->adaptation_count ?? 0,
                ];
            }
        }

        // Decode captured descriptor
        $capturedVector = is_array($capturedDescriptor)
            ? $capturedDescriptor
            : json_decode($capturedDescriptor, true);

        if (!is_array($capturedVector) || count($capturedVector) !== 192) {
            return [
                'adapted' => false,
                'reason' => 'Format vektor biometrik tidak valid (wajib array 192 elemen numerik).',
                'confidence_score' => $normalizedConfidence,
                'drift_to_master' => null,
                'adaptation_count' => $embedding?->adaptation_count ?? 0,
            ];
        }

        // Decode Master Anchor
        $masterVector = json_decode($masterDescriptorStr, true);
        if (!is_array($masterVector) || count($masterVector) !== 192) {
            return [
                'adapted' => false,
                'reason' => 'Master Anchor biometrik di database rusak atau tidak valid.',
                'confidence_score' => $normalizedConfidence,
                'drift_to_master' => null,
                'adaptation_count' => $embedding?->adaptation_count ?? 0,
            ];
        }

        // Tentukan template dasar sebelumnya (jika sudah pernah adaptasi, gunakan adaptive; jika belum, gunakan master)
        $previousVector = ($embedding && !empty($embedding->adaptive_descriptor_mobile))
            ? json_decode($embedding->adaptive_descriptor_mobile, true)
            : $masterVector;

        if (!is_array($previousVector) || count($previousVector) !== 192) {
            $previousVector = $masterVector;
        }

        // --- EXPONENTIAL MOVING AVERAGE (EMA) BLENDING ---
        // Formula: E_new = (1 - alpha) * E_prev + alpha * E_captured
        $alpha = self::DEFAULT_LEARNING_RATE_ALPHA;
        $blendedVector = [];
        for ($i = 0; $i < 192; $i++) {
            $blendedVector[$i] = ((1.0 - $alpha) * (float)$previousVector[$i]) + ($alpha * (float)$capturedVector[$i]);
        }

        // Normalisasi Euclidean L2
        $normalizedBlendedVector = $this->l2Normalize($blendedVector);

        // --- GATE 4: Drift Guard (Anchor Sanity Check) ---
        // Ukur kemiripan vektor baru hasil blend terhadap Master Anchor asli
        $driftSim = $this->cosineSimilarity($normalizedBlendedVector, $masterVector);

        if ($driftSim < self::DRIFT_GUARD_THRESHOLD) {
            Log::warning("Biometric Drift Guard triggered for personnel {$personnel->id} ({$personnel->name}). Drift similarity to master: {$driftSim} < " . self::DRIFT_GUARD_THRESHOLD);
            return [
                'adapted' => false,
                'reason' => "Gate 4 (Drift Guard) ditolak: Kemiripan vektor terhadap Master Anchor (" . round($driftSim * 100, 1) . "%) di bawah batas aman (" . (self::DRIFT_GUARD_THRESHOLD * 100) . "%). Adaptasi dibatalkan otomatis untuk menjaga integritas data.",
                'confidence_score' => $normalizedConfidence,
                'drift_to_master' => $driftSim,
                'adaptation_count' => $embedding?->adaptation_count ?? 0,
            ];
        }

        // --- SIMPAN TEMPLATE ADAPTIF ---
        if (!$embedding) {
            $embedding = new PersonnelFaceEmbedding([
                'personnel_id' => $personnel->id,
                'pose_type' => $poseType,
                'face_descriptor_mobile' => $masterDescriptorStr,
            ]);
        }

        $newAdaptationCount = ($embedding->adaptation_count ?? 0) + 1;
        $embedding->adaptive_descriptor_mobile = json_encode($normalizedBlendedVector);
        $embedding->adaptation_count = $newAdaptationCount;
        $embedding->last_adapted_at = Carbon::now();
        $embedding->save();

        // Simpan log audit
        try {
            PersonnelFaceLearningLog::create([
                'personnel_id' => $personnel->id,
                'pose_type' => $poseType,
                'absensi_id' => $absensiId,
                'confidence_score' => $normalizedConfidence,
                'drift_to_master' => $driftSim,
                'adaptation_index' => $newAdaptationCount,
                'device_info' => $deviceInfo,
            ]);
        } catch (\Throwable $e) {
            Log::error("Gagal mencatat audit log adaptasi biometrik: " . $e->getMessage());
        }

        Log::info("Biometric adaptation SUCCESS for personnel {$personnel->id} ({$personnel->name}) pose {$poseType}. Adapt count: {$newAdaptationCount}, Confidence: {$normalizedConfidence}, DriftSim: {$driftSim}");

        return [
            'adapted' => true,
            'reason' => "AI Pembelajaran Mandiri berhasil mengadaptasi template wajah ({$newAdaptationCount}x adaptasi, drift similarity: " . round($driftSim * 100, 1) . "%).",
            'confidence_score' => $normalizedConfidence,
            'drift_to_master' => $driftSim,
            'adaptation_count' => $newAdaptationCount,
        ];
    }

    /**
     * Mengembalikan template adaptif ke Master Anchor asli (Reset to Master).
     *
     * @param Personnel $personnel
     * @param string|null $poseType Jika null, semua pose akan direset
     * @return array
     */
    public function resetToMaster(Personnel $personnel, ?string $poseType = null): array
    {
        $query = $personnel->faceEmbeddings();
        if ($poseType) {
            $query->where('pose_type', $poseType);
        }

        $updatedCount = $query->update([
            'adaptive_descriptor_mobile' => null,
            'adaptation_count' => 0,
            'last_adapted_at' => null,
        ]);

        PersonnelVectorUpdated::dispatch(
            $personnel->id,
            $personnel->opd_id,
            'reset_adaptive'
        );

        Log::info("Reset to Master executed for personnel {$personnel->id} ({$personnel->name}). Updated records: {$updatedCount}");

        return [
            'success' => true,
            'message' => "Template adaptif biometrik berhasil direset ke Master Anchor asli untuk {$personnel->name}.",
            'updated_count' => $updatedCount,
        ];
    }

    /**
     * Menghitung Cosine Similarity antara dua vektor n-D.
     */
    public function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        $n = count($vecA);

        for ($i = 0; $i < $n; $i++) {
            $a = (float)$vecA[$i];
            $b = (float)$vecB[$i];
            $dot += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Melakukan normalisasi Euclidean L2 pada vektor.
     */
    public function l2Normalize(array $vec): array
    {
        $sumSq = 0.0;
        foreach ($vec as $val) {
            $f = (float)$val;
            $sumSq += $f * $f;
        }

        $norm = sqrt($sumSq);
        if ($norm <= 1e-12) {
            return $vec;
        }

        return array_map(fn($v) => (float)$v / $norm, $vec);
    }
}
