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

        $payload = [
            "message" => [

                "token" => $deviceToken,

                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],

                "data" => $data
            ]
        ];

        return Http::withToken($accessToken)
            ->post($url, $payload)
            ->json();
    }
}
