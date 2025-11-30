<?php

namespace App\Services;

use App\Models\Integration;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;

class GoogleCalendarService
{
    public function __construct(private ?Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    public function createOrUpdateEvent(Integration $integration, array $payload): string
    {
        $accessToken = $this->ensureAccessToken($integration);
        if (!$accessToken) {
            Log::warning('Google sync skipped: no access token', ['user_id' => $integration->user_id]);
            return $payload['provider_event_id'] ?? '';
        }

        $calendarId = $integration->calendar_id ?: 'primary';

        $startDate = Carbon::parse($payload['start']);
        $endDate = $startDate->copy()->addDay();

        $event = [
            'summary' => $payload['summary'] ?? 'Evento financiero',
            'description' => $payload['description'] ?? '',
            'start' => [
                'date' => $startDate->toDateString(),
                'timeZone' => config('app.timezone', 'UTC'),
            ],
            'end' => [
                'date' => $endDate->toDateString(),
                'timeZone' => config('app.timezone', 'UTC'),
            ],
        ];

        $eventId = $payload['provider_event_id'] ?? null;
        $method = $eventId ? 'PATCH' : 'POST';
        $url = $eventId
            ? "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/{$eventId}"
            : "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events";

        try {
            $response = $this->client->request($method, $url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $event,
                'http_errors' => false,
            ]);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $data = json_decode((string) $response->getBody(), true);
                return $data['id'] ?? ($eventId ?: '');
            }

            Log::warning('Google sync failed', [
                'status' => $response->getStatusCode(),
                'body' => (string) $response->getBody(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Google sync exception', ['message' => $e->getMessage()]);
        }

        return $eventId ?: '';
    }

    private function ensureAccessToken(Integration $integration): ?string
    {
        if ($integration->expires_at && $integration->expires_at->isFuture() && $integration->access_token) {
            return $integration->access_token;
        }

        return $this->refreshAccessToken($integration);
    }

    private function refreshAccessToken(Integration $integration): ?string
    {
        if (!$integration->refresh_token) {
            return null;
        }

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        try {
            $response = $this->client->request('POST', 'https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $integration->refresh_token,
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ],
                'http_errors' => false,
            ]);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $data = json_decode((string) $response->getBody(), true);
                $integration->update([
                    'access_token' => $data['access_token'] ?? null,
                    'expires_at' => isset($data['expires_in']) ? now()->addSeconds($data['expires_in']) : null,
                ]);

                return $integration->access_token;
            }

            Log::warning('Google token refresh failed', [
                'status' => $response->getStatusCode(),
                'body' => (string) $response->getBody(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Google token refresh exception', ['message' => $e->getMessage()]);
        }

        return null;
    }

    public function deleteEvent(Integration $integration, string $eventId): bool
    {
        $accessToken = $this->ensureAccessToken($integration);
        if (!$accessToken || !$eventId) {
            return false;
        }

        $calendarId = $integration->calendar_id ?: 'primary';
        $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/{$eventId}";

        try {
            $response = $this->client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                ],
                'http_errors' => false,
            ]);

            return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
        } catch (\Throwable $e) {
            Log::error('Google delete event exception', ['message' => $e->getMessage()]);
        }

        return false;
    }
}
