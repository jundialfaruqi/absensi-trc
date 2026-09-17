<?php

namespace App\Http\Controllers\Api\V1\Personel;

use App\Events\PersonnelLocationUpdated;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PersonnelLocationController extends Controller
{
    /**
     * Update real-time location and presence for personnel device.
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Koordinat lokasi tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        /** @var \App\Models\Device|null $device */
        $device = $request->attributes->get('device');
        /** @var \App\Models\Personnel|null $personnel */
        $personnel = $request->attributes->get('personnel');

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Informasi perangkat tidak ditemukan.',
            ], 404);
        }

        $device->update([
            'last_latitude' => $request->latitude,
            'last_longitude' => $request->longitude,
            'last_seen_at' => now(),
        ]);

        broadcast(new PersonnelLocationUpdated(
            $personnel?->id ?? $device->personnel_id,
            $request->latitude,
            $request->longitude,
            now()->diffForHumans(),
            $device->id
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi perangkat berhasil diperbarui.',
        ]);
    }
}
