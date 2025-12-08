<?php

namespace App\Jobs;

use App\Models\ShoppingList;
use App\Services\ShoppingListEventLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeShoppingMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected ?int $userId = null
    ) {
    }

    public function handle(ShoppingListEventLogger $logger): void
    {
        $query = ShoppingList::query()->with('items');

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        $windowStart = now('America/Bogota')->copy()->subDays(7);

        $query->chunk(50, function ($lists) use ($logger, $windowStart) {
            foreach ($lists as $list) {
                $dailyTotals = $list->items
                    ->filter(fn ($item) => $item->checked_at && $item->checked_at->greaterThanOrEqualTo($windowStart))
                    ->groupBy(fn ($item) => $item->checked_at->timezone('America/Bogota')->toDateString())
                    ->map(fn ($group) => [
                        'cop_total' => round($group->sum(fn ($item) => (float) ($item->actual_price ?? 0)), 2),
                        'items' => $group->count(),
                    ])
                    ->all();

                $inventoryGap = $list->items->sum(function ($item) {
                    $target = (float) ($item->qty_to_buy_base ?? 0);
                    $current = (float) ($item->qty_current_base ?? 0);
                    return max(0, $target - $current);
                });

                $pendingItems = $list->items->where('is_checked', false)->count();

                $logger->log($list, 'weekly_metrics', [
                    'daily_totals' => $dailyTotals,
                    'inventory_gap_base' => round($inventoryGap, 3),
                    'pending_items' => $pendingItems,
                    'cop_total_checked' => round($list->items->sum(fn ($item) => (float) ($item->actual_price ?? 0)), 2),
                ]);
            }
        });
    }
}
