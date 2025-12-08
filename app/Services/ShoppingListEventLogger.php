<?php

namespace App\Services;

use App\Models\ShoppingList;
use App\Models\ShoppingListEvent;

class ShoppingListEventLogger
{
    public function log(ShoppingList $list, string $event, array $payload = []): ShoppingListEvent
    {
        $timestamp = now('America/Bogota');

        $basePayload = [
            'logged_at' => $timestamp->toIso8601String(),
            'timezone' => 'America/Bogota',
            'currency' => 'COP',
            'list_status' => $list->status,
        ];

        return $list->events()->create([
            'event' => $event,
            'payload' => array_merge($basePayload, $payload),
        ]);
    }
}
