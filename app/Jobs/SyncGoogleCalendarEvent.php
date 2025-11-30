<?php

namespace App\Jobs;

use App\Models\Integration;
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
        $service->createOrUpdateEvent($this->integration, $this->payload);
    }
}
