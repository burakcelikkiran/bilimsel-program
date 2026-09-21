<?php

namespace App\Services;

use App\Models\EventDevice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FcmSender
{
    public function isConfigured(): bool
    {
        $path = config('firebase.credentials');
        $projectId = config('firebase.project_id');

        return is_string($path)
            && $path !== ''
            && is_readable($path)
            && is_string($projectId)
            && $projectId !== '';
    }

    /**
     * @param  array<int, string>  $tokens
     * @param  array<string, string>  $data
     * @return array{sent: int, failed: int}
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $tokens = array_values(array_unique(array_filter($tokens)));
        $sent = 0;
        $failed = 0;

        if ($tokens === [] || ! $this->isConfigured()) {
            if ($tokens !== [] && ! $this->isConfigured()) {
                Log::warning('FCM skipped: Firebase credentials are not configured.');
            }

            return ['sent' => 0, 'failed' => 0];
        }

        $accessToken = $this->accessToken();
        if ($accessToken === null) {
            return ['sent' => 0, 'failed' => count($tokens)];
        }

        $projectId = config('firebase.project_id');
        $endpoint = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        $androidNotification = $this->androidNotificationPayload();

        foreach ($tokens as $token) {
            try {
                $message = [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
                    'android' => [
                        'priority' => 'high',
                    ],
                ];

                if ($androidNotification !== []) {
                    $message['android']['notification'] = $androidNotification;
                }

                $imageUrl = config('firebase.notification_image_url');
                if (is_string($imageUrl) && $imageUrl !== '') {
                    $message['notification']['image'] = $imageUrl;
                }

                $response = Http::withToken($accessToken)
                    ->acceptJson()
                    ->timeout(15)
                    ->post($endpoint, [
                        'message' => $message,
                    ]);

                if ($response->successful()) {
                    $sent++;

                    continue;
                }

                $failed++;
                $errorStatus = data_get($response->json(), 'error.status')
                    ?? data_get($response->json(), 'error.details.0.errorCode');

                if ($this->isInvalidToken($response->status(), is_string($errorStatus) ? $errorStatus : null)) {
                    EventDevice::query()->where('token', $token)->delete();
                } else {
                    Log::warning('FCM send failed', [
                        'status' => $response->status(),
                        'body' => $response->json(),
                    ]);
                }
            } catch (Throwable $e) {
                $failed++;
                Log::error('FCM send exception', ['message' => $e->getMessage()]);
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    /**
     * @return array<string, string>
     */
    private function androidNotificationPayload(): array
    {
        $payload = [
            'channel_id' => 'tpk2026_default',
            'color' => '#c41e3a',
        ];

        $icon = config('firebase.android_notification_icon');
        if (is_string($icon) && $icon !== '') {
            $payload['icon'] = $icon;
        }

        $imageUrl = config('firebase.notification_image_url');
        if (is_string($imageUrl) && $imageUrl !== '') {
            $payload['image'] = $imageUrl;
        }

        return $payload;
    }

    private function isInvalidToken(int $status, ?string $errorStatus): bool
    {
        if ($status === 404) {
            return true;
        }

        $needle = strtoupper((string) $errorStatus);

        return in_array($needle, ['NOT_FOUND', 'UNREGISTERED', 'INVALID_ARGUMENT'], true);
    }

    private function accessToken(): ?string
    {
        $credentials = $this->credentials();
        if ($credentials === null) {
            return null;
        }

        $now = time();
        $jwt = $this->encodeJwt([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], $credentials['private_key']);

        $response = Http::asForm()
            ->timeout(15)
            ->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

        if (! $response->successful()) {
            Log::error('FCM OAuth token failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return null;
        }

        $token = $response->json('access_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    /**
     * @return array{client_email: string, private_key: string}|null
     */
    private function credentials(): ?array
    {
        $path = config('firebase.credentials');
        if (! is_string($path) || $path === '' || ! is_readable($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            return null;
        }

        $email = $decoded['client_email'] ?? null;
        $key = $decoded['private_key'] ?? null;

        if (! is_string($email) || $email === '' || ! is_string($key) || $key === '') {
            return null;
        }

        return [
            'client_email' => $email,
            'private_key' => $key,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encodeJwt(array $payload, string $privateKey): string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $body = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $data = $header.'.'.$body;

        $ok = openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (! $ok) {
            throw new \RuntimeException('Unable to sign FCM JWT.');
        }

        return $data.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
