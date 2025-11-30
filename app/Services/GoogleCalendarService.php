<?php

namespace App\Services;

use App\Models\Integration;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    public function createOrUpdateEvent(Integration $integration, array $payload): string
    {
        // TODO: Implement real Google Calendar API call.
        Log::info('Sync stub: would sync event to Google Calendar', [
            'user_id' => $integration->user_id,
            'summary' => $payload['summary'] ?? '',
            'start' => $payload['start'] ?? null,
        ]);

        return $payload['provider_event_id'] ?? 'stub-event-id';
    }
}
