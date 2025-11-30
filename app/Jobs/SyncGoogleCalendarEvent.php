<?php

namespace App\Jobs;

use App\Models\Integration;
use App\Models\Budget;
use App\Models\Recurrence;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncGoogleCalendarEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Integration $integration,
        protected array $payload
    ) {
    }

    public function handle(GoogleCalendarService $service): void
    {
        $eventId = $service->createOrUpdateEvent($this->integration, $this->payload);

        if (!empty($this->payload['model']) && !empty($this->payload['model_id'])) {
            if ($this->payload['model'] === 'budget') {
                Budget::where('id', $this->payload['model_id'])->update([
                    'provider_event_id' => $eventId,
                    'last_synced_at' => now(),
                ]);
            }

            if ($this->payload['model'] === 'recurrence') {
                Recurrence::where('id', $this->payload['model_id'])->update([
                    'provider_event_id' => $eventId,
                    'last_synced_at' => now(),
                ]);
            }
        }
    }
}
