<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use App\Models\FoodLocation;
use App\Models\FoodProduct;
use App\Models\FoodPurchase;
use App\Models\FoodPurchaseItem;
use App\Models\FoodType;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\Wallet;
use App\Services\FoodFinanceService;
use App\Services\UnitConverter;
use App\Services\ShoppingListEventLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $baseListsQuery = ShoppingList::withFullDetails()
            ->withCount([
                'items as pending_inventory_count' => fn($q) => $q->where('is_checked', true)
                    ->whereRaw("JSON_EXTRACT(metadata, '$.inventory_batch_id') IS NULL")
            ])
            ->where('user_id', $userId);

        $lists = (clone $baseListsQuery)
            ->active()
            ->recent(12)
            ->get();

        if ($lists->isEmpty()) {
            $lists = (clone $baseListsQuery)
                ->notArchived()
                ->recent(12)
                ->get();
        }

        $selectedList = $lists->firstWhere('id', (int) $request->input('list_id')) ?? $lists->first();

        $pendingInventoryItems = collect();
        if ($selectedList) {
            $pendingInventoryItems = $selectedList->items
                ->filter(fn ($item) => $item->is_checked && empty(data_get($item->metadata, 'inventory_batch_id')))
                ->sortByDesc('checked_at')
                ->values();
        }

        return view('food.purchases.index', [
            'purchases' => FoodPurchase::with([
                    'items:id,purchase_id,product_id,qty,unit,subtotal',
                    'items.product:id,name,brand'
                ])
                ->select('id', 'user_id', 'occurred_on', 'vendor', 'total')
                ->where('user_id', $userId)
                ->orderByDesc('occurred_on')
                ->limit(50)
                ->get(),
            'wallets' => Wallet::where('user_id', $userId)->orderBy('name')->get(),
            'categories' => Category::where('user_id', $userId)->where('type', 'expense')->orderBy('name')->get(),
            'budgets' => Budget::where('user_id', $userId)->orderByDesc('year')->orderByDesc('month')->limit(24)->get(),
            'products' => FoodProduct::where('user_id', $userId)->orderBy('name')->get(),
            'locations' => FoodLocation::where('user_id', $userId)->orderBy('sort_order')->get(),
            'types' => FoodType::where('user_id', $userId)->where('is_active', true)->orderBy('sort_order')->get(),
            'lists' => $lists,
            'selectedList' => $selectedList,
            'listItems' => $selectedList?->items ?? collect(),
            'pendingInventoryItems' => $pendingInventoryItems,
            'pendingInventoryCount' => $pendingInventoryItems->count(),
        ]);
    }

    public function store(
        Request $request,
        UnitConverter $converter,
        FoodFinanceService $finance
    ): RedirectResponse {
        $userId = $request->user()->id;

        $data = $request->validate([
            'shopping_list_id' => 'required|exists:sogar_shopping_lists,id',
            'occurred_on' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'wallet_id' => 'nullable|exists:sogar_wallets,id',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.list_item_id' => 'required|integer',
            'items.*.include' => 'nullable|boolean',
            'items.*.qty' => 'nullable|numeric|min:0.001',
            'items.*.unit' => 'nullable|string|max:16',
            'items.*.unit_size' => 'nullable|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.location_id' => 'nullable|exists:sogar_food_locations,id',
        ]);

        $list = ShoppingList::with('items.product')
            ->where('user_id', $userId)
            ->findOrFail($data['shopping_list_id']);

        $eventLogger = app(ShoppingListEventLogger::class);

        $items = collect($data['items'])
            ->filter(fn ($item) => !empty($item['include']))
            ->values();

        if ($items->isEmpty()) {
            return back()
                ->withErrors(['items' => 'Selecciona al menos un ítem de la lista.'])
                ->withInput();
        }

        // Iniciar transacción para garantizar integridad de datos
        \DB::beginTransaction();

        try {
            $purchase = new FoodPurchase([
                'user_id' => $userId,
                'wallet_id' => $data['wallet_id'] ?? null,
                'occurred_on' => $data['occurred_on'],
                'vendor' => $data['vendor'] ?? null,
                'receipt_number' => $data['receipt_number'] ?? null,
                'note' => $data['note'] ?? null,
            ]);

            $total = 0;

            $purchase->save();

        $listItems = $list->items->keyBy('id');

        $expectedTotal = 0;

        foreach ($items as $item) {
            $listItem = $listItems->get((int) $item['list_item_id']);

            if (!$listItem) {
                continue;
            }

            $expectedTotal += (float) ($listItem->estimated_price ?? 0);

            $unitSize = $item['unit_size'] ?? $listItem->unit_size ?? 1;
            $unitLabel = $item['unit'] ?? $listItem->unit_base ?? 'unit';
            $qty = $item['qty'] ?? $listItem->qty_to_buy_base ?? 1;

            $subtotal = round($qty * $item['unit_price'], 2);
            $total += $subtotal;

            $productId = $listItem->product_id;
            if (!$productId && !empty($item['name'])) {
                $productId = FoodProduct::create([
                    'user_id' => $userId,
                    'name' => $listItem->name,
                    'type_id' => $listItem->product?->type_id,
                    'default_location_id' => $item['location_id'] ?? $listItem->location_id,
                    'unit_base' => $unitLabel,
                    'unit_size' => $unitSize,
                ])->id;
            }

            $locationId = $item['location_id'] ?? $listItem->location_id ?? $listItem->product?->default_location_id;
            $categoryId = $listItem->category_id ?? null;
            $budgetId = $list->budget_id ?? null;

            $purchaseItem = FoodPurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $productId,
                'type_id' => $listItem->product?->type_id,
                'location_id' => $locationId,
                'category_id' => $categoryId,
                'budget_id' => $budgetId,
                'qty' => $qty,
                'unit' => $unitLabel,
                'unit_size' => $unitSize,
                'unit_price' => $item['unit_price'],
                'subtotal' => $subtotal,
                'expires_on' => $listItem->metadata['expires_on'] ?? null,
            ]);

            $qtyBase = $converter->toBase($unitLabel, $qty, $unitSize);
            $pricePerBase = $converter->pricePerBase($unitLabel, $qty, $unitSize, $subtotal);

            $purchaseItem->product?->prices()->create([
                'source' => 'manual',
                'vendor' => $purchase->vendor,
                'currency' => 'COP',
                'price_per_base' => $pricePerBase,
                'captured_on' => ($purchase->occurred_on ?? Carbon::today())->timezone('America/Bogota'),
            ]);

            $batch = null;
            if ($purchaseItem->product_id) {
                $productForBatch = $purchaseItem->product ?? FoodProduct::find($purchaseItem->product_id);

                if ($productForBatch) {
                    $batch = $productForBatch->batches()->create([
                        'user_id' => $userId,
                        'location_id' => $locationId,
                        'purchase_item_id' => $purchaseItem->id,
                        'qty_base' => $qtyBase,
                        'qty_remaining_base' => $qtyBase,
                        'unit_base' => $productForBatch->unit_base ?? $unitLabel,
                        'expires_on' => $purchaseItem->expires_on,
                        'entered_on' => ($purchase->occurred_on ?? Carbon::today())->timezone('America/Bogota'),
                        'cost_total' => $subtotal,
                        'currency' => 'COP',
                        'status' => 'ok',
                    ]);
                }
            }

            $checkedAt = now('America/Bogota');
            $metadata = $listItem->metadata ?? [];
            $metadata['added_to_inventory'] = true;
            $metadata['added_at'] = $checkedAt->toIso8601String();
            if ($batch) {
                $metadata['inventory_batch_id'] = $batch->id;
            }

            $listItem->fill([
                'is_checked' => true,
                'checked_at' => $checkedAt,
                'actual_price' => $subtotal,
                'qty_current_base' => ($listItem->qty_current_base ?? 0) + $qtyBase,
            ]);
            $listItem->metadata = $metadata;
            $listItem->save();

            $eventLogger->log($list, 'item_checked', [
                'item_id' => $listItem->id,
                'product_id' => $listItem->product_id,
                'is_checked' => true,
                'qty_to_buy_base' => (float) ($listItem->qty_to_buy_base ?? 0),
                'actual_price' => (float) $subtotal,
                'estimated_price' => (float) ($listItem->estimated_price ?? 0),
                'cop_delta' => round($subtotal - ($listItem->estimated_price ?? 0), 2),
                'source' => 'purchase',
            ]);

            if ($batch) {
                $eventLogger->log($list, 'inventory_batch_created', [
                    'item_id' => $listItem->id,
                    'batch_id' => $batch->id,
                    'product_id' => $batch->product_id,
                    'qty_base' => (float) $batch->qty_base,
                    'cost_total' => (float) $batch->cost_total,
                    'source' => 'purchase',
                ]);
            } else {
                $eventLogger->log($list, 'inventory_discrepancy', [
                    'item_id' => $listItem->id,
                    'reason' => $listItem->product_id ? 'batch_not_created' : 'missing_product',
                ]);
            }
        }

        $purchase->update(['total' => $total]);

        $remaining = $list->items()->where(function ($query) {
            $query->whereNull('is_checked')
                ->orWhere('is_checked', false);
        })->count();

        $list->actual_total = $list->items->sum(function ($item) {
            return $item->is_checked ? ($item->actual_price ?? $item->estimated_price ?? 0) : 0;
        });
        if ($remaining === 0) {
            $list->status = 'completed';
        }
        $list->save();

        $eventLogger->log($list, 'purchase_recorded', [
            'purchase_id' => $purchase->id,
            'total_cop' => (float) $total,
            'expected_total_cop' => round($expectedTotal, 2),
            'difference_cop' => round($total - $expectedTotal, 2),
            'remaining_items' => $remaining,
            'occurred_on' => Carbon::parse($data['occurred_on'])->timezone('America/Bogota')->toDateString(),
        ]);

        if ($request->boolean('impact_finanzas')) {
            $finance->registerExpenseFromPurchase($purchase);
        }

            \DB::commit();

            return redirect()->route('food.purchases.index')->with('status', 'Compra registrada.');

        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('Error al registrar compra: ' . $e->getMessage(), [
                'user_id' => $userId,
                'list_id' => $data['shopping_list_id'],
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withErrors(['error' => 'Error al procesar la compra. Intenta nuevamente.'])
                ->withInput();
        }
    }

}
