<?php

namespace App\Services;

use App\Models\FirebaseDevice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FirebaseMessaging
{
    public function send(FirebaseDevice $device, array $notification): void
    {
        $credentials = $this->credentials();
        if ($credentials === null) {
            throw new RuntimeException('Firebase push cannot be sent because FIREBASE_CREDENTIALS is not configured.');
        }

        $projectId = config('services.firebase.project_id') ?: ($credentials['project_id'] ?? null);
        if (! is_string($projectId) || $projectId === '') {
            throw new RuntimeException('Firebase project ID is missing from configuration and service-account credentials.');
        }

        $response = Http::timeout(15)
            ->withToken($this->accessToken($credentials))
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $device->token,
                    'notification' => [
                        'title' => $notification['title'],
                        'body' => $notification['body'],
                    ],
                    'data' => [
                        'click_url' => $notification['url'],
                    ],
                    'webpush' => [
                        'fcm_options' => ['link' => $notification['url']],
                        'notification' => ['icon' => url('/favicon.ico')],
                    ],
                ],
            ]);

        if ($response->status() === 404 && str_contains($response->body(), 'UNREGISTERED')) {
            $device->delete();

            return;
        }

        $response->throw();
    }

    private function credentials(): ?array
    {
        $path = config('services.firebase.credentials');
        if (! is_string($path) || $path === '') {
            return null;
        }

        $isAbsolutePath = str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
        $path = $isAbsolutePath ? $path : base_path($path);
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('Firebase service-account file is missing or unreadable: '.$path);
        }

        $contents = file_get_contents($path);
        if (! is_string($contents)) {
            throw new RuntimeException('Firebase service-account file could not be read.');
        }

        $credentials = json_decode($contents, true);
        if (! is_array($credentials)) {
            throw new RuntimeException('Firebase service-account file must contain valid JSON.');
        }

        foreach (['client_email', 'private_key', 'token_uri'] as $requiredKey) {
            if (! is_string($credentials[$requiredKey] ?? null) || $credentials[$requiredKey] === '') {
                throw new RuntimeException("Firebase service-account credentials are missing {$requiredKey}.");
            }
        }

        return $credentials;
    }

    private function accessToken(array $credentials): string
    {
        $cacheKey = 'firebase-fcm:access-token:'.sha1($credentials['client_email']);

        return Cache::remember($cacheKey, now()->addMinutes(55), function () use ($credentials) {
            $issuedAt = time();
            $jwt = $this->signedJwt([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $credentials['token_uri'],
                'iat' => $issuedAt,
                'exp' => $issuedAt + 3600,
            ], $credentials['private_key']);

            $response = Http::asForm()->timeout(15)->post($credentials['token_uri'], [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ])->throw();

            $accessToken = $response->json('access_token');
            if (! is_string($accessToken) || $accessToken === '') {
                throw new RuntimeException('Google OAuth did not return a Firebase access token.');
            }

            return $accessToken;
        });
    }

    private function signedJwt(array $claims, string $privateKey): string
    {
        $encode = static fn (string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        $header = $encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = $encode(json_encode($claims, JSON_THROW_ON_ERROR));
        $unsignedToken = $header.'.'.$payload;

        if (! openssl_sign($unsignedToken, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Could not sign the Firebase service-account OAuth assertion.');
        }

        return $unsignedToken.'.'.$encode($signature);
    }
}
