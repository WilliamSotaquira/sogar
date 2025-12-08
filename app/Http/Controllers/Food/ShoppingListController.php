<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\FoodProduct;
use App\Models\FoodStockBatch;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Services\ShoppingListGenerator;
use App\Services\ShoppingListSyncService;
use App\Services\ShoppingListEventLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShoppingListController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status', 'active');
        $baseQuery = $this->accessibleListsQuery($request);

        $listQuery = (clone $baseQuery)->with('items');
        if ($statusFilter === 'completed') {
            $listQuery->where('status', 'completed');
        } else {
            $statusFilter = 'active';
            $listQuery->where('status', 'active');
        }

        $list = $listQuery
            ->latest('generated_at')
            ->first();

        $recentLists = (clone $baseQuery)
            ->orderByDesc('generated_at')
            ->limit(10)
            ->get();

        $activeCount = (clone $baseQuery)->where('status', 'active')->count();
        $completedCount = (clone $baseQuery)->where('status', 'completed')->count();
        $pendingItems = $list?->items?->where('is_checked', false)->count() ?? 0;

        return view('food.shopping-list.index', [
            'list' => $list,
            'recentLists' => $recentLists,
            'statusFilter' => $statusFilter,
            'activeCount' => $activeCount,
            'completedCount' => $completedCount,
            'pendingItems' => $pendingItems,
        ]);
    }

    /**
     * Mostrar todas las listas de compra del usuario
     */
    public function all(Request $request): View
    {
        $lists = $this->accessibleListsQuery($request)
            ->with(['items', 'budget.category', 'familyGroup'])
            ->orderBy('created_at', 'desc')
            ->get();

        $budgets = \App\Models\Budget::where('user_id', $request->user()->id)
            ->with('category')
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->get();

        return view('food.shopping-list.all', [
            'lists' => $lists,
            'budgets' => $budgets,
        ]);
    }

    public function generate(
        Request $request,
        ShoppingListGenerator $generator
    ): RedirectResponse {
        $data = $request->validate([
            'horizon_days' => 'nullable|integer|min:1|max:30',
            'people_count' => 'nullable|integer|min:1|max:10',
            'safety_factor' => 'nullable|numeric|min:1|max:2',
            'name' => 'nullable|string|max:255',
            'list_type' => 'nullable|string|in:general,food,cleaning,maintenance,other',
            'expected_purchase_on' => 'nullable|date',
            'budget_id' => 'nullable|exists:sogar_budgets,id',
            'category_id' => 'nullable|exists:sogar_categories,id',
            'auto_suggest' => 'nullable|boolean',
        ]);

        // Verificar que el presupuesto pertenece al usuario si se proporciona
        $budget = null;
        if (!empty($data['budget_id'])) {
            $budget = \App\Models\Budget::where('id', $data['budget_id'])
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        }

        // No cerrar lista activa, permitir múltiples listas activas
        // ShoppingList::where('user_id', $request->user()->id)
        //     ->where('status', 'active')
        //     ->update(['status' => 'closed']);

        $listName = $data['name'] ?? 'Compra ' . now()->format('d/m/Y');

        // Si auto_suggest está activo y no hay budget, crear lista vacía
        if ($request->boolean('auto_suggest')) {
            $list = ShoppingList::create([
                'user_id' => $request->user()->id,
                'family_group_id' => $request->user()->active_family_group_id,
                'name' => $listName,
                'list_type' => $data['list_type'] ?? 'general',
                'status' => 'active',
                'generated_at' => now(),
                'expected_purchase_on' => $data['expected_purchase_on'] ?? now()->addDays(7),
                'budget_id' => $budget?->id,
                'category_id' => $data['category_id'] ?? $budget?->category_id,
            ]);
        } else {
            $list = $generator->generate(
                $request->user()->id,
                $data['horizon_days'] ?? 7,
                $data['people_count'] ?? 3,
                $data['safety_factor'] ?? 1.2,
                $listName,
                $data['expected_purchase_on'] ?? null
            );

            // Asignar presupuesto, categoría y tipo a la lista
            $list->update([
                'list_type' => $data['list_type'] ?? 'general',
                'budget_id' => $budget?->id,
                'category_id' => $data['category_id'] ?? $budget?->category_id,
                'family_group_id' => $request->user()->active_family_group_id,
            ]);
        }

        $this->logListEvent($list, 'list_created', [
            'list_type' => $list->list_type,
            'budget_id' => $list->budget_id,
            'category_id' => $list->category_id,
            'estimated_budget' => (float) ($list->estimated_budget ?? 0),
        ]);

        return redirect()->route('food.shopping-list.show', $list)
            ->with('status', 'Lista creada correctamente.');
    }

    public function update(Request $request, ShoppingList $list): RedirectResponse
    {
        $this->authorizeList($request, $list, 'manage');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'expected_purchase_on' => 'nullable|date',
            'budget_id' => 'nullable|exists:sogar_budgets,id',
            'is_collaborative' => 'nullable|boolean',
        ]);

        // Si se cambia el presupuesto, verificar que pertenezca al usuario
        if (!empty($data['budget_id'])) {
            \App\Models\Budget::where('id', $data['budget_id'])
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        }

        $list->update([
            'name' => $data['name'],
            'expected_purchase_on' => $data['expected_purchase_on'] ?? $list->expected_purchase_on,
            'budget_id' => $data['budget_id'] ?? $list->budget_id,
            'is_collaborative' => $data['is_collaborative'] ?? $list->is_collaborative,
        ]);

        return back()->with('status', 'Lista actualizada.');
    }

    public function markItem(Request $request, ShoppingList $list, int $itemId): RedirectResponse
    {
        $this->authorizeList($request, $list);

        $item = $list->items()->where('id', $itemId)->firstOrFail();
        $previousChecked = (bool) $item->is_checked;
        $isChecked = $request->boolean('is_checked');

        // Actualizar cantidad si se proporciona
        $qtyUpdate = $request->input('qty_to_buy_base');
        if ($qtyUpdate !== null && is_numeric($qtyUpdate)) {
            $item->qty_to_buy_base = (float) $qtyUpdate;
        }

        // Registrar precio real si se proporciona
        $actualPrice = $request->input('actual_price');
        if ($actualPrice !== null && is_numeric($actualPrice)) {
            $item->actual_price = (float) $actualPrice;

            // Registrar cambio de precio en el historial
            if ($item->product_id) {
                $priceService = app(\App\Services\PriceChangeService::class);
                $vendor = $request->input('vendor_name', $item->vendor_name);

                $priceService->registerPriceChange(
                    $item->product,
                    $actualPrice / $item->qty_to_buy_base,
                    $vendor,
                    'purchase',
                    "Registrado desde lista de compras: {$list->name}"
                );
            }
        }

        // Registrar vendor si se proporciona
        $vendorName = $request->input('vendor_name');
        if ($vendorName) {
            $item->vendor_name = $vendorName;
        }

        $item->is_checked = $isChecked;

        if ($isChecked) {
            $item->checked_at = now();
        } else {
            $item->checked_at = null;
        }

        $item->save();

        // Si se marca como comprado, ingresar al inventario (una sola vez)
        $meta = $item->metadata ?? [];
        $alreadyAdded = $meta['added_to_inventory'] ?? false;
        $nowInBogota = now('America/Bogota');
        if ($isChecked && !$alreadyAdded && $item->product_id) {
            $product = $item->product ?: FoodProduct::find($item->product_id);
            $locationId = $item->location_id ?? $product?->default_location_id;
            $unitBase = $item->unit_base ?: $product?->unit_base ?: 'unit';
            $unitSize = $item->unit_size ?: $product?->unit_size ?: 1;
            $qtyBase = $item->qty_to_buy_base ?? 0;
            $expiresAt = $product?->shelf_life_days
                ? $nowInBogota->copy()->addDays($product->shelf_life_days)
                : null;

            $batch = FoodStockBatch::create([
                'user_id' => $request->user()->id,
                'product_id' => $item->product_id,
                'location_id' => $locationId,
                'qty_base' => $qtyBase,
                'qty_remaining_base' => $qtyBase,
                'unit_base' => $unitBase,
                'entered_on' => $nowInBogota->toDateString(),
                'expires_on' => $expiresAt?->toDateString(),
                'status' => 'ok',
                'cost_total' => $item->actual_price ?? $item->estimated_price ?? 0,
                'currency' => 'COP',
            ]);

            $meta['added_to_inventory'] = true;
            $meta['added_at'] = $nowInBogota->toIso8601String();
            $meta['inventory_batch_id'] = $batch->id;
            $item->metadata = $meta;
            $item->save();

            $this->logListEvent($list, 'inventory_batch_created', [
                'item_id' => $item->id,
                'batch_id' => $batch->id,
                'product_id' => $item->product_id,
                'qty_base' => (float) $qtyBase,
                'cost_total' => (float) ($item->actual_price ?? $item->estimated_price ?? 0),
                'source' => 'list_mark',
            ]);
        }

        // Actualizar total de la lista
        $this->updateListTotal($list);

        $this->logListEvent($list, 'item_checked', [
            'item_id' => $item->id,
            'product_id' => $item->product_id,
            'is_checked' => $item->is_checked,
            'qty_to_buy_base' => (float) ($item->qty_to_buy_base ?? 0),
            'actual_price' => (float) ($item->actual_price ?? 0),
            'estimated_price' => (float) ($item->estimated_price ?? 0),
            'cop_delta' => round(($item->actual_price ?? 0) - ($item->estimated_price ?? 0), 2),
        ]);

        $metaAfter = $item->metadata ?? [];
        if ($isChecked && empty($metaAfter['inventory_batch_id'])) {
            $reason = $item->product_id ? 'pending_batch' : 'missing_product';

            $this->logListEvent($list, 'inventory_discrepancy', [
                'item_id' => $item->id,
                'reason' => $reason,
            ]);
        }

        return back();
    }

    public function sync(Request $request, ShoppingListSyncService $sync): RedirectResponse
    {
        $list = $this->accessibleListsQuery($request)
            ->with('items')
            ->where('status', 'active')
            ->firstOrFail();
        $this->authorizeList($request, $list);
        $sync->syncToInventory($list, $request->input('wallet_id'));

        return back()->with('status', 'Lista sincronizada a inventario.');
    }

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'list_id' => 'nullable|exists:sogar_shopping_lists,id',
            'name' => 'required|string|max:255',
            'qty_to_buy_base' => 'required|numeric|min:0.001',
            'priority' => 'nullable|string|max:16',
            'product_id' => 'nullable|exists:sogar_food_products,id',
            // Datos para crear producto si no existe
            'create_product' => 'nullable|boolean',
            'brand' => 'nullable|string|max:255',
            'type_id' => 'nullable|exists:sogar_food_types,id',
            'location_id' => 'nullable|exists:sogar_food_locations,id',
            'unit_base' => 'nullable|string|max:16',
            'unit_size' => 'nullable|numeric|min:0.001',
            'min_stock_qty' => 'nullable|numeric|min:0',
            'shelf_life_days' => 'nullable|integer|min:1',
            'barcode' => 'nullable|string|max:255',
        ]);

        $list = null;
        if (!empty($data['list_id'])) {
            $list = $this->accessibleListsQuery($request)
                ->where('id', $data['list_id'])
                ->first();
        }
        if (!$list) {
            $list = $this->accessibleListsQuery($request)
                ->where('status', 'active')
                ->latest('generated_at')
                ->first();
        }

        if (!$list) {
            $list = ShoppingList::create([
                'user_id' => $request->user()->id,
                'family_group_id' => $request->user()->active_family_group_id,
                'name' => 'Lista de compra ' . now()->format('d/m'),
                'status' => 'active',
                'generated_at' => now(),
                'expected_purchase_on' => now()->addDays(7),
            ]);
        }

        $this->authorizeList($request, $list);

        $productId = $data['product_id'] ?? null;
        $productStock = 0;

        // Si se solicita crear producto y no hay product_id
        if ($request->boolean('create_product') && !$productId) {
            $product = FoodProduct::create([
                'user_id' => $request->user()->id,
                'name' => $data['name'],
                'brand' => $data['brand'] ?? null,
                'type_id' => $data['type_id'] ?? null,
                'default_location_id' => $data['location_id'] ?? null,
                'unit_base' => $data['unit_base'] ?? 'unit',
                'unit_size' => $data['unit_size'] ?? 1,
                'min_stock_qty' => $data['min_stock_qty'] ?? null,
                'shelf_life_days' => $data['shelf_life_days'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'is_active' => true,
            ]);

            $productId = $product->id;
            $productStock = 0;
        } elseif ($productId) {
            $productStock = \App\Models\FoodStockBatch::where('product_id', $productId)
                ->where('status', 'ok')
                ->sum('qty_remaining_base');
        }

        $nextSortOrder = ($list->items()->max('sort_order') ?? -1) + 1;

        $item = ShoppingListItem::create([
            'shopping_list_id' => $list->id,
            'name' => $data['name'],
            'product_id' => $productId,
            'location_id' => $data['location_id'] ?? null,
            'qty_to_buy_base' => $data['qty_to_buy_base'],
            'qty_suggested_base' => $data['qty_to_buy_base'],
            'unit_base' => $data['unit_base'] ?? 'unit',
            'unit_size' => $data['unit_size'] ?? 1,
            'priority' => $data['priority'] ?? 'medium',
            'is_checked' => false,
            'sort_order' => $nextSortOrder,
            'qty_current_base' => $productStock,
        ]);

        $payload = [
            'status' => 'ok',
            'item' => $item->load('product'),
            'stock_ok' => $productStock >= $data['qty_to_buy_base'],
            'product_created' => $request->boolean('create_product') && $productId,
        ];

        if ($request->wantsJson()) {
            return response()->json($payload, 201);
        }

        return back()->with('status', 'Producto agregado' . ($payload['product_created'] ? ' y creado en catálogo' : '') . '.');
    }

    public function bulkAction(Request $request, ShoppingList $list): RedirectResponse
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*' => 'integer|exists:sogar_shopping_list_items,id',
            'action' => 'required|string|in:delete,mark,unmark',
        ]);

        $this->authorizeList($request, $list, $data['action'] === 'delete' ? 'manage' : 'view');

        $items = $list->items()->whereIn('id', $data['items'])->get();

        if ($data['action'] === 'delete') {
            foreach ($items as $item) {
                $item->delete();
            }
            return back()->with('status', 'Productos eliminados.');
        }

        $markValue = $data['action'] === 'mark';
        foreach ($items as $item) {
            $item->is_checked = $markValue;
            $item->save();
        }

        return back()->with('status', $markValue ? 'Productos marcados como comprados.' : 'Productos desmarcados.');
    }

    public function destroyItem(Request $request, ShoppingList $list, ShoppingListItem $item): RedirectResponse
    {
        $this->authorizeList($request, $list, 'manage');
        abort_unless($item->shopping_list_id === $list->id, 404);
        $item->delete();

        return back()->with('status', 'Producto eliminado de la lista.');
    }

    /**
     * Generar sugeridos automáticos basados en stock bajo
     */
    public function generateSuggestions(Request $request, ShoppingList $list)
    {
        $this->authorizeList($request, $list);

        // Obtener productos con stock bajo
        $products = FoodProduct::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->get()
            ->filter(function ($product) {
                $currentStock = \App\Models\FoodStockBatch::where('product_id', $product->id)
                    ->where('status', 'ok')
                    ->sum('qty_remaining_base');

                return $product->min_stock_qty > 0 && $currentStock < $product->min_stock_qty;
            });

        $count = 0;
        foreach ($products as $product) {
            // Verificar si ya está en la lista
            $exists = ShoppingListItem::where('shopping_list_id', $list->id)
                ->where('product_id', $product->id)
                ->exists();

            if (!$exists) {
                $currentStock = \App\Models\FoodStockBatch::where('product_id', $product->id)
                    ->where('status', 'ok')
                    ->sum('qty_remaining_base');

                $qtyToBuy = max(1, $product->min_stock_qty - $currentStock);

                // Obtener precio actual
                $latestPrice = \App\Models\FoodPrice::where('product_id', $product->id)
                    ->orderBy('captured_on', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();

                ShoppingListItem::create([
                    'shopping_list_id' => $list->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'qty_to_buy_base' => $qtyToBuy,
                    'qty_current_base' => $currentStock,
                    'unit_base' => $product->unit_base,
                    'estimated_price' => $latestPrice ? $latestPrice->price_per_base * $qtyToBuy : 0,
                    'low_stock_alert' => true,
                    'is_checked' => false,
                    'sort_order' => ShoppingListItem::where('shopping_list_id', $list->id)->max('sort_order') + 1,
                ]);

                $count++;
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => "$count productos sugeridos agregados",
            ]);
        }

        return back()->with('status', "$count productos sugeridos agregados a la lista.");
    }

    public function destroy(Request $request, ShoppingList $list): RedirectResponse
    {
        $this->authorizeList($request, $list, 'manage');

        if ($list->status === 'active') {
            return back()->with('status', 'No se puede eliminar la lista activa. Genera una nueva primero.');
        }

        $list->delete();

        return back()->with('status', 'Lista eliminada.');
    }

    public function searchProducts(Request $request)
    {
        $term = $request->input('q', '');
        if (strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $products = FoodProduct::where('user_id', $request->user()->id)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'barcode']);

        $products->transform(function ($p) {
            $stock = \App\Models\FoodStockBatch::where('product_id', $p->id)->where('status', 'ok')->sum('qty_remaining_base');
            $p->stock = $stock;
            return $p;
        });

        return response()->json(['data' => $products]);
    }

    private function updateListTotal(ShoppingList $list): void
    {
        // Sumar precios reales de items marcados, o estimados si no hay real
        $total = $list->items->sum(function ($item) {
            if ($item->is_checked) {
                return $item->actual_price ?? $item->estimated_price ?? 0;
            }
            return 0;
        });

        $list->actual_total = $total;
        $list->save();
    }

    /**
     * Consulta base para listas accesibles por el usuario (propias o de su núcleo activo).
     */
    private function accessibleListsQuery(Request $request)
    {
        $user = $request->user();
        $familyGroupIds = $user->familyGroupIds();

        if ($user->isSystemAdmin()) {
            return ShoppingList::query();
        }

        return ShoppingList::where(function ($query) use ($user, $familyGroupIds) {
            $query->where('user_id', $user->id);

            if (!empty($familyGroupIds)) {
                $query->orWhereIn('family_group_id', $familyGroupIds);
            }
        });
    }

    /**
     * Autoriza acceso o gestión de una lista considerando familia compartida.
     */
    private function authorizeList(Request $request, ShoppingList $list, string $ability = 'view'): void
    {
        $user = $request->user();
        $isOwner = $list->user_id === $user->id;
        $inFamily = $list->family_group_id ? $user->canAccessFamilyGroup($list->family_group_id) : false;

        if (!$isOwner && !$inFamily && !$user->isSystemAdmin()) {
            abort(403);
        }

        if ($ability === 'manage' && !$isOwner && !$user->isSystemAdmin()) {
            abort_unless($list->family_group_id && $user->isAdminOfFamilyGroup($list->family_group_id), 403);
        }
    }

    public function show(Request $request, ShoppingList $list): View
    {
        $this->authorizeList($request, $list);

        return view('food.shopping-list.show', [
            'list' => $list->load('items'),
        ]);
    }

    private function logListEvent(ShoppingList $list, string $event, array $payload = []): void
    {
        app(ShoppingListEventLogger::class)->log($list, $event, $payload);
    }
}
