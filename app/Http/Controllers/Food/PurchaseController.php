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
use App\Models\Wallet;
use App\Services\FoodFinanceService;
use App\Services\UnitConverter;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        return view('food.purchases.index', [
            'purchases' => FoodPurchase::with('items.product')
                ->where('user_id', $userId)
                ->orderByDesc('occurred_on')
                ->limit(50)
                ->get(),
            'wallets' => Wallet::where('user_id', $userId)->get(),
            'categories' => Category::where('user_id', $userId)->where('type', 'expense')->get(),
            'budgets' => Budget::where('user_id', $userId)->orderByDesc('year')->orderByDesc('month')->limit(24)->get(),
            'products' => FoodProduct::where('user_id', $userId)->orderBy('name')->get(),
            'locations' => FoodLocation::where('user_id', $userId)->orderBy('sort_order')->get(),
            'types' => FoodType::where('user_id', $userId)->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(
        Request $request,
        UnitConverter $converter,
        FoodFinanceService $finance
    ): RedirectResponse {
        $userId = $request->user()->id;

        $data = $request->validate([
            'occurred_on' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'wallet_id' => 'nullable|exists:sogar_wallets,id',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:sogar_food_products,id',
            'items.*.type_id' => 'nullable|exists:sogar_food_types,id',
            'items.*.location_id' => 'nullable|exists:sogar_food_locations,id',
            'items.*.category_id' => 'nullable|exists:sogar_categories,id',
            'items.*.budget_id' => 'nullable|exists:sogar_budgets,id',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.qty' => 'required|numeric|min:0.001',
            'items.*.unit' => 'required|string|max:16',
            'items.*.unit_size' => 'nullable|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.expires_on' => 'nullable|date',
        ]);

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

        foreach ($data['items'] as $item) {
            $unitSize = $item['unit_size'] ?? 1;
            $subtotal = round($item['qty'] * $item['unit_price'], 2);
            $total += $subtotal;

            $productId = $item['product_id'] ?? null;
            if (!$productId && !empty($item['name'])) {
                $productId = FoodProduct::create([
                    'user_id' => $userId,
                    'name' => $item['name'],
                    'type_id' => $item['type_id'] ?? null,
                    'default_location_id' => $item['location_id'] ?? null,
                    'unit_base' => $item['unit'],
                    'unit_size' => $unitSize,
                ])->id;
            }

            $purchaseItem = FoodPurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $productId,
                'type_id' => $item['type_id'] ?? null,
                'location_id' => $item['location_id'] ?? null,
                'category_id' => $item['category_id'] ?? null,
                'budget_id' => $item['budget_id'] ?? null,
                'qty' => $item['qty'],
                'unit' => $item['unit'],
                'unit_size' => $unitSize,
                'unit_price' => $item['unit_price'],
                'subtotal' => $subtotal,
                'expires_on' => $item['expires_on'] ?? null,
            ]);

            $qtyBase = $converter->toBase($item['unit'], $item['qty'], $unitSize);
            $pricePerBase = $converter->pricePerBase($item['unit'], $item['qty'], $unitSize, $subtotal);

            $purchaseItem->product?->prices()->create([
                'source' => 'manual',
                'vendor' => $purchase->vendor,
                'currency' => 'USD',
                'price_per_base' => $pricePerBase,
                'captured_on' => $purchase->occurred_on ?? Carbon::today(),
            ]);

            if ($purchaseItem->product_id) {
                $purchaseItem->product->batches()->create([
                    'user_id' => $userId,
                    'location_id' => $purchaseItem->location_id,
                    'purchase_item_id' => $purchaseItem->id,
                    'qty_base' => $qtyBase,
                    'qty_remaining_base' => $qtyBase,
                    'unit_base' => $purchaseItem->product->unit_base,
                    'expires_on' => $purchaseItem->expires_on,
                    'entered_on' => $purchase->occurred_on ?? Carbon::today(),
                    'cost_total' => $subtotal,
                    'currency' => 'USD',
                    'status' => 'ok',
                ]);
            }
        }

        $purchase->update(['total' => $total]);

        if ($request->boolean('impact_finanzas')) {
            $finance->registerExpenseFromPurchase($purchase);
        }

        return redirect()->route('food.purchases.index')->with('status', 'Compra registrada.');
    }
}
