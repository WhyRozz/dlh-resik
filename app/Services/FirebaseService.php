<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Http;

class FirebaseService
{
    protected string $projectId;

    protected string $credentials;

    public function __construct()
    {
        $this->projectId = config('firebase.project_id');

        $this->credentials = base_path(
            config('firebase.credentials')
        );
    }

    private function getAccessToken()
    {
        $client = new Client();

        $client->setAuthConfig($this->credentials);

        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();

        return $token['access_token'];
    }

    public function sendNotification($deviceToken, $title, $body, array $data = [])
    {
        $accessToken = $this->getAccessToken();
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        // 1. Siapkan payload notification
        $notificationPayload = [
            "title" => $title,
            "body"  => $body,
        ];

        // Pindahkan 'image' dari data ke notification
        if (!empty($data['image'])) {
            $notificationPayload['image'] = $data['image'];
        }

        // 2. Konfigurasi khusus Android (WAJIB untuk pop-up/heads-up)
        $androidNotification = [
            "notification_priority" => "PRIORITY_MAX", // Agar notifikasi pop-up
            "sound"                 => "default",
            "default_vibrate_timings" => true,
            "visibility"            => "PUBLIC",
            "channel_id"            => "high_importance_channel", // ID Channel
        ];

        // Pindahkan 'icon' dari data ke android.notification
        if (!empty($data['icon'])) {
            $androidNotification['icon'] = $data['icon'];
        }

        // 3. Susun Payload FCM v1 (LENGKAP UNTUK ANDROID & WEB)
        $payload = [
            "message" => [
                "token" => $deviceToken,
                "notification" => [
                    "title" => $title,
                    "body"  => $body,
                ],
                "data" => $data,
                
                // ✅ TAMBAHKAN INI: Konfigurasi khusus agar Web Browser menampilkan 1x notifikasi yang rapi
                "webpush" => [
                    "headers" => [
                        "Urgency" => "high",
                    ],
                    "notification" => [
                        "title" => $title,
                        "body"  => $body,
                        "icon"  => $data['icon'] ?? '/icons/Icon-192.png',
                        "tag"   => (string)($data['id'] ?? 'resik-notif') // ✅ per-pesan, tidak saling timpa
                    ]
                ],

                // ✅ Konfigurasi Android (HP)
                "android" => [
                    "priority" => "high",
                    "notification" => [
                        "channel_id"            => "high_importance_channel",
                        "notification_priority" => "PRIORITY_MAX",
                        "sound"                 => "default",
                        "visibility"            => "public",
                        "icon"                  => $data['icon'] ?? null,
                        "image"                 => $data['image'] ?? null,
                    ],
                ],
            ]
        ];

        return Http::withToken($accessToken)
            ->post($url, $payload)
            ->json();
    }
}
