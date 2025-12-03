<?php

namespace App\Services;

use App\Models\FoodPurchase;
use App\Models\FoodPurchaseItem;
use App\Models\ShoppingList;
use Illuminate\Support\Facades\DB;

class ShoppingListSyncService
{
    public function syncToInventory(ShoppingList $list, ?int $walletId = null): void
    {
        $userId = $list->user_id;

        DB::transaction(function () use ($list, $userId, $walletId) {
            $purchase = FoodPurchase::create([
                'user_id' => $userId,
                'wallet_id' => $walletId,
                'occurred_on' => now()->toDateString(),
                'vendor' => 'Compra lista: ' . ($list->name ?? $list->id),
                'total' => 0,
                'currency' => 'USD',
            ]);

            $total = 0;
            foreach ($list->items as $item) {
                $qty = $item->qty_to_buy_base ?: $item->qty_suggested_base;
                $unitSize = $item->unit_size ?: 1;
                $unit = $item->unit_base ?: 'unit';
                $subtotal = ($item->estimated_price ?? 0);
                $total += $subtotal;

                $purchaseItem = FoodPurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item->product_id,
                    'type_id' => $item->product?->type_id,
                    'location_id' => $item->location_id ?? $item->product?->default_location_id,
                    'category_id' => null,
                    'budget_id' => null,
                    'qty' => $qty / $unitSize,
                    'unit' => $unit,
                    'unit_size' => $unitSize,
                    'unit_price' => $subtotal > 0 && $qty > 0 ? $subtotal / $qty : 0,
                    'subtotal' => $subtotal,
                    'expires_on' => null,
                ]);

                if ($purchaseItem->product_id) {
                    $purchaseItem->product->batches()->create([
                        'user_id' => $userId,
                        'location_id' => $purchaseItem->location_id,
                        'purchase_item_id' => $purchaseItem->id,
                        'qty_base' => $qty,
                        'qty_remaining_base' => $qty,
                        'unit_base' => $unit,
                        'entered_on' => $purchase->occurred_on,
                        'cost_total' => $subtotal,
                        'currency' => 'USD',
                        'status' => 'ok',
                    ]);
                }
            }

            $purchase->update(['total' => $total]);

            $list->update(['status' => 'closed']);
            $list->events()->create(['event' => 'synced', 'payload' => ['purchase_id' => $purchase->id]]);
        });
    }
}
