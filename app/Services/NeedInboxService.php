<?php

namespace App\Services;

use App\Models\FoodProduct;
use App\Models\FoodStockBatch;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\User;
use App\Services\ShoppingListEventLogger;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NeedInboxService
{
    public function __construct(private ShoppingListEventLogger $eventLogger)
    {
    }

    public function addFoodNeed(
        User $user,
        string $query,
        ?string $unitBase = null,
        bool $confirmNew = false
    ): array
    {
        $term = trim($query);
        if ($term === '') {
            throw ValidationException::withMessages([
                'query' => ['Ingresa un nombre o código de barras.'],
            ]);
        }

        $barcode = $this->extractBarcode($term);
        $product = $this->findProduct($user, $term, $barcode);
        $productCreated = false;

        if (!$product) {
            if (!$confirmNew) {
                throw ValidationException::withMessages([
                    'confirm_new' => ['Producto nuevo. Confirma unidad base.'],
                ]);
            }

            $product = $this->createProduct($user, $term, $barcode, $unitBase);
            $productCreated = true;
        }

        $list = $this->getActiveList($user);
        if (!$list) {
            $list = ShoppingList::create([
                'user_id' => $user->id,
                'family_group_id' => $user->active_family_group_id,
                'name' => 'Compra semanal - ' . now()->locale('es')->translatedFormat('j M'),
                'status' => 'active',
                'generated_at' => now(),
                'expected_purchase_on' => now()->addDays(7),
            ]);
        }

        $item = $list->items()
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->qty_to_buy_base = (float) $item->qty_to_buy_base + 1;
            $item->qty_suggested_base = (float) $item->qty_to_buy_base;
            $item->save();

            $this->eventLogger->log($list, 'item_quantity_updated', [
                'item_id' => $item->id,
                'product_id' => $product->id,
                'source' => 'need_inbox',
            ]);

            return [
                'item' => $item,
                'message' => 'Ya estaba en la lista, sumé 1.',
                'action' => 'incremented',
                'product_created' => $productCreated,
            ];
        }

        $productStock = FoodStockBatch::where('product_id', $product->id)
            ->where('status', 'ok')
            ->sum('qty_remaining_base');

        $nextSortOrder = ($list->items()->max('sort_order') ?? -1) + 1;

        $item = ShoppingListItem::create([
            'shopping_list_id' => $list->id,
            'name' => $product->name,
            'product_id' => $product->id,
            'location_id' => $product->default_location_id,
            'qty_to_buy_base' => 1,
            'qty_suggested_base' => 1,
            'unit_base' => $product->unit_base ?? 'unit',
            'unit_size' => $product->unit_size ?? 1,
            'priority' => 'medium',
            'is_checked' => false,
            'sort_order' => $nextSortOrder,
            'qty_current_base' => $productStock,
        ]);

        $this->eventLogger->log($list, 'item_added', [
            'item_id' => $item->id,
            'product_id' => $product->id,
            'source' => 'need_inbox',
            'product_created' => $productCreated,
        ]);

        return [
            'item' => $item,
            'message' => $productCreated ? 'Producto creado y agregado.' : 'Agregado a la lista activa.',
            'action' => 'added',
            'product_created' => $productCreated,
        ];
    }

    private function extractBarcode(string $term): ?string
    {
        $onlyDigits = preg_replace('/\D+/', '', $term);
        if ($onlyDigits === '' || strlen($onlyDigits) < 8) {
            return null;
        }

        return $onlyDigits;
    }

    private function findProduct(User $user, string $term, ?string $barcode): ?FoodProduct
    {
        $query = FoodProduct::where('user_id', $user->id)->active();

        if ($barcode) {
            $match = (clone $query)->where('barcode', $barcode)->first();
            if ($match) {
                return $match;
            }
        }

        return $query
            ->whereRaw('LOWER(name) = ?', [Str::lower($term)])
            ->first();
    }

    private function createProduct(User $user, string $term, ?string $barcode, ?string $unitBase): FoodProduct
    {
        $name = $term;
        if ($barcode && preg_replace('/\D+/', '', $term) === $barcode) {
            $name = 'Producto ' . $barcode;
        }

        return FoodProduct::create([
            'user_id' => $user->id,
            'name' => $name,
            'barcode' => $barcode,
            'unit_base' => $unitBase ?: 'unit',
            'unit_size' => 1,
            'min_stock_qty' => null,
            'is_active' => true,
        ]);
    }

    private function getActiveList(User $user): ?ShoppingList
    {
        $familyGroupIds = $user->familyGroupIds();

        return ShoppingList::where(function ($query) use ($user, $familyGroupIds) {
            $query->where('user_id', $user->id);
            if (!empty($familyGroupIds)) {
                $query->orWhereIn('family_group_id', $familyGroupIds);
            }
        })
            ->where('status', 'active')
            ->latest('generated_at')
            ->first();
    }
}
