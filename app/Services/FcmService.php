<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service to handle Firebase Cloud Messaging (FCM) HTTP v1 API.
 * Uses native PHP OpenSSL and JWT to authenticate with Google OAuth2.
 */
class FcmService
{
    private string $serviceAccountPath;

    public function __construct()
    {
        $this->serviceAccountPath = storage_path('app/firebase-service-account.json');
    }

    /**
     * Send a push notification to a specific device token.
     */
    public function sendNotification(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            if (! file_exists($this->serviceAccountPath)) {
                Log::error('FcmService: firebase-service-account.json not found at '.$this->serviceAccountPath);

                return false;
            }

            $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);
            if (! $serviceAccount || ! isset($serviceAccount['project_id'])) {
                Log::error('FcmService: Invalid firebase-service-account.json structure.');

                return false;
            }

            $projectId = $serviceAccount['project_id'];
            $accessToken = $this->getAccessToken($serviceAccount);

            if (! $accessToken) {
                Log::error('FcmService: Failed to retrieve Google OAuth2 access token.');

                return false;
            }

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // Modern FCM HTTP v1 Payload format with custom sound and channel
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'android' => [
                        'notification' => [
                            'sound' => 'reminder_sound',
                            'channel_id' => 'attendance_reminder_channel',
                        ],
                    ],
                    'data' => array_merge($data, [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ]),
                ],
            ];

            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error('FcmService: Failed to send FCM. Response: '.$response->body());

            return false;
        } catch (\Exception $e) {
            Log::error('FcmService Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Retrieve the Google OAuth2 access token (cached for 55 minutes).
     */
    private function getAccessToken(array $serviceAccount): ?string
    {
        return Cache::remember('fcm_oauth_access_token', 3300, function () use ($serviceAccount): ?string {
            $jwt = $this->generateJwt($serviceAccount);
            if (! $jwt) {
                return null;
            }

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('FcmService OAuth Error: '.$response->body());

            return null;
        });
    }

    /**
     * Generate JWT signed with RSA-SHA256 for Google OAuth2 assertion.
     */
    private function generateJwt(array $serviceAccount): ?string
    {
        $privateKey = $serviceAccount['private_key'] ?? null;
        $clientEmail = $serviceAccount['client_email'] ?? null;

        if (! $privateKey || ! $clientEmail) {
            return null;
        }

        $now = time();
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $claimSet = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ]);

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlClaimSet = $this->base64UrlEncode($claimSet);

        $signatureInput = $base64UrlHeader.'.'.$base64UrlClaimSet;
        $signature = '';

        if (! openssl_sign($signatureInput, $signature, $privateKey, 'SHA256')) {
            Log::error('FcmService: Failed to sign JWT with OpenSSL.');

            return null;
        }

        return $signatureInput.'.'.$this->base64UrlEncode($signature);
    }

    /**
     * Base64 URL safe encoding helper.
     */
    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
