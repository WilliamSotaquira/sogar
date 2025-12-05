<?php

namespace App\Services;

use App\Models\ConsumptionLog;
use App\Models\FoodPrice;
use App\Models\FoodProduct;
use App\Models\FoodStockBatch;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShoppingListGenerator
{
    public function generate(
        int $userId,
        int $horizonDays = 7,
        int $peopleCount = 3,
        float $safetyFactor = 1.2,
        ?string $name = null,
        ?string $expectedPurchaseOn = null,
    ): ShoppingList {
        return DB::transaction(function () use ($userId, $horizonDays, $peopleCount, $safetyFactor, $name, $expectedPurchaseOn) {
            // Cerrar lista activa previa
            ShoppingList::where('user_id', $userId)->where('status', 'active')->update(['status' => 'closed']);

            $list = ShoppingList::create([
                'user_id' => $userId,
                'name' => $name ?: ('Lista de compra ' . Carbon::now()->format('d/m')),
                'status' => 'active',
                'generated_at' => Carbon::now(),
                'expected_purchase_on' => $expectedPurchaseOn ?: Carbon::now()->addDays($horizonDays),
                'people_count' => $peopleCount,
                'purchase_frequency_days' => $horizonDays,
                'safety_factor' => $safetyFactor,
            ]);

            $products = FoodProduct::with(['batches' => function ($q) {
                $q->where('status', 'ok');
            }])->where('user_id', $userId)->where('is_active', true)->get();

            $items = [];
            foreach ($products as $product) {
                $consumption = $this->consumptionDailyAverage($userId, $product->id);
                $consumption = $consumption ?: max(0, ($product->min_stock_qty ?? 0) / 30);

                $need = $consumption * $horizonDays * $peopleCount;
                $inventory = $product->batches->sum('qty_remaining_base');
                $threshold = $consumption * $horizonDays * $safetyFactor;

                $expiresSoon = $product->batches
                    ->filter(fn ($b) => $b->expires_on && Carbon::parse($b->expires_on)->lte(Carbon::now()->addDays(3)))
                    ->isNotEmpty();

                if ($inventory >= $threshold && !$expiresSoon) {
                    continue;
                }

                $toBuy = max($need - $inventory, 0);
                if ($toBuy <= 0 && !$expiresSoon) {
                    continue;
                }

                $price = FoodPrice::where('product_id', $product->id)->latest()->value('price_per_base');
                $estimated = $price ? $price * $toBuy : 0;

                $priority = $inventory < ($threshold * 0.3) || $expiresSoon ? 'high' : ($inventory < ($threshold * 0.6) ? 'medium' : 'low');

                $items[] = [
                    'shopping_list_id' => $list->id,
                    'name' => $product->name,
                    'product_id' => $product->id,
                    'category_id' => $product->type_id ? null : null,
                    'location_id' => $product->default_location_id,
                    'priority' => $priority,
                    'qty_suggested_base' => $need,
                    'qty_current_base' => $inventory,
                    'qty_to_buy_base' => $toBuy,
                    'qty_unit_label' => $this->formatQty($product->unit_base, $product->unit_size, $toBuy),
                    'unit_base' => $product->unit_base,
                    'unit_size' => $product->unit_size,
                    'estimated_price' => round($estimated, 2),
                    'is_checked' => false,
                    'barcode' => $product->barcode,
                    'sort_order' => count($items),
                    'metadata' => json_encode([
                        'expires_soon' => $expiresSoon,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($items) {
                $list->items()->insert($items);
            }

            $list->update([
                'estimated_budget' => collect($items)->sum('estimated_price'),
            ]);

            $list->events()->create([
                'event' => 'generated',
                'payload' => [
                    'items' => count($items),
                    'horizon_days' => $horizonDays,
                    'people_count' => $peopleCount,
                    'safety_factor' => $safetyFactor,
                ],
            ]);

            return $list->load('items');
        });
    }

    private function consumptionDailyAverage(int $userId, int $productId, int $days = 30): float
    {
        $from = Carbon::now()->subDays($days);
        $sum = ConsumptionLog::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('occurred_on', '>=', $from->toDateString())
            ->sum('qty_base');

        return $days > 0 ? ($sum / $days) : 0;
    }

    private function formatQty(?string $unit, ?float $unitSize, float $base): string
    {
        $unit = $unit ?: 'unit';
        $unitSize = $unitSize ?: 1;
        $value = $base / $unitSize;
        if (in_array($unit, ['g', 'ml']) && $value >= 1000) {
            $value = $value / 1000;
            $unit = $unit === 'g' ? 'kg' : 'l';
        }

        return round($value, 2) . ' ' . $unit;
    }
}
