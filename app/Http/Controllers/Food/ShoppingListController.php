<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\FoodProduct;
use App\Models\FoodStockBatch;
use App\Models\Category;
use App\Models\FoodLocation;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\ShoppingListType;
use App\Services\ShoppingListGenerator;
use App\Services\ShoppingListSyncService;
use App\Services\ShoppingListEventLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShoppingListController extends Controller
{
    public function index(Request $request): View
    {
        ShoppingListType::ensureDefaultsForUser($request->user()->id);
        $listTypes = ShoppingListType::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['name', 'slug']);

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
            'listTypes' => $listTypes,
            'locations' => \App\Models\FoodLocation::where('user_id', $request->user()->id)->orderBy('sort_order')->get(),
            'types' => \App\Models\FoodType::where('user_id', $request->user()->id)->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Mostrar todas las listas de compra del usuario
     */
    public function all(Request $request): View
    {
        ShoppingListType::ensureDefaultsForUser($request->user()->id);
        $listTypes = ShoppingListType::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['name', 'slug']);

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
            'listTypes' => $listTypes,
        ]);
    }

    public function generate(
        Request $request,
        ShoppingListGenerator $generator
    ): RedirectResponse {
        ShoppingListType::ensureDefaultsForUser($request->user()->id);

        $data = $request->validate([
            'horizon_days' => 'nullable|integer|min:1|max:30',
            'people_count' => 'nullable|integer|min:1|max:10',
            'safety_factor' => 'nullable|numeric|min:1|max:2',
            'name' => 'nullable|string|max:255',
            'list_type' => [
                'required',
                'string',
                'max:50',
                Rule::exists('sogar_shopping_list_types', 'slug')
                    ->where('user_id', $request->user()->id)
                    ->where('is_active', true),
            ],
            'expected_purchase_on' => 'nullable|date',
            'budget_id' => 'nullable|exists:sogar_budgets,id',
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

        $listTypeLabel = ShoppingListType::where('slug', $data['list_type'])
            ->where('user_id', $request->user()->id)
            ->value('name');
        $dateLabel = now()->locale('es')->translatedFormat('j M');
        $defaultName = ($listTypeLabel ?: 'Compra semanal') . ' - ' . $dateLabel;
        $listName = trim((string) ($data['name'] ?? '')) !== '' ? $data['name'] : $defaultName;

        // Si auto_suggest está activo y no hay budget, crear lista vacía
        if ($request->boolean('auto_suggest')) {
            $list = ShoppingList::create([
                'user_id' => $request->user()->id,
                'family_group_id' => $request->user()->active_family_group_id,
                'name' => $listName,
                'list_type' => $data['list_type'],
                'status' => 'active',
                'generated_at' => now(),
                'expected_purchase_on' => $data['expected_purchase_on'] ?? now()->addDays(7),
                'budget_id' => $budget?->id,
                'category_id' => null,
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
                'list_type' => $data['list_type'],
                'budget_id' => $budget?->id,
                'category_id' => null,
                'family_group_id' => $request->user()->active_family_group_id,
            ]);
        }

        $this->logListEvent($list, 'list_created', [
            'list_type' => $list->list_type,
            'budget_id' => $list->budget_id,
            'category_id' => null,
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

        $wantsJson = $request->wantsJson() || $request->expectsJson();
        $productCreated = false;

        // Buscar producto por nombre o código de barras
        $product = \App\Models\FoodProduct::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->where(function($q) use ($data) {
                $q->where('name', $data['name'])
                  ->orWhere('barcode', $data['name']);
            })
            ->first();

        if (!$product) {
            if ($request->boolean('create_product')) {
                $creation = $request->validate([
                    'unit_base' => 'required|string|max:16',
                    'barcode' => [
                        'nullable',
                        'string',
                        'max:255',
                        Rule::unique('sogar_food_products', 'barcode')
                            ->where('user_id', $request->user()->id),
                    ],
                ]);

                $product = FoodProduct::create([
                    'user_id' => $request->user()->id,
                    'name' => trim($data['name']),
                    'brand' => $data['brand'] ?? null,
                    'type_id' => $data['type_id'] ?? null,
                    'default_location_id' => $data['location_id'] ?? null,
                    'unit_base' => $creation['unit_base'],
                    'unit_size' => (float) ($data['unit_size'] ?? 1),
                    'min_stock_qty' => (float) ($data['min_stock_qty'] ?? 1),
                    'shelf_life_days' => $data['shelf_life_days'] ?? null,
                    'barcode' => $creation['barcode'] ?? null,
                    'is_active' => true,
                ]);
                $productCreated = true;
            } else {
                $message = 'El producto no existe en el catálogo. Activa "Crear nuevo" para agregarlo al instante.';
                if ($wantsJson) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $message,
                        'errors' => ['name' => [$message]],
                    ], 422);
                }

                return back()->withErrors(['name' => $message])->withInput();
            }
        }

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
                'name' => 'Compra semanal - ' . now()->locale('es')->translatedFormat('j M'),
                'status' => 'active',
                'generated_at' => now(),
                'expected_purchase_on' => now()->addDays(7),
            ]);
        }

        $this->authorizeList($request, $list);

        $productId = $product->id;
        $productStock = \App\Models\FoodStockBatch::where('product_id', $productId)
            ->where('status', 'ok')
            ->sum('qty_remaining_base');

        $nextSortOrder = ($list->items()->max('sort_order') ?? -1) + 1;

        $item = ShoppingListItem::create([
            'shopping_list_id' => $list->id,
            'name' => $product->name,
            'product_id' => $productId,
            'location_id' => $product->default_location_id,
            'qty_to_buy_base' => $data['qty_to_buy_base'],
            'qty_suggested_base' => $data['qty_to_buy_base'],
            'unit_base' => $product->unit_base ?? 'unit',
            'unit_size' => $product->unit_size ?? 1,
            'priority' => $data['priority'] ?? 'medium',
            'is_checked' => false,
            'sort_order' => $nextSortOrder,
            'qty_current_base' => $productStock,
        ]);

        $payload = [
            'status' => 'ok',
            'item' => $item->load('product'),
            'stock_ok' => $productStock >= $data['qty_to_buy_base'],
            'product_created' => $productCreated,
        ];

        if ($wantsJson) {
            return response()->json($payload, 201);
        }

        return back()->with('status', 'Producto agregado' . ($payload['product_created'] ? ' y creado en catálogo' : '') . '.');
    }

    /**
     * Alternar estado de un ítem (solo checkbox)
     */
    public function toggleItem(Request $request, ShoppingList $list, int $itemId)
    {
        $this->authorizeList($request, $list);

        $item = $list->items()->where('id', $itemId)->firstOrFail();
        $isChecked = $request->input('is_checked', false);

        $item->is_checked = (bool) $isChecked;
        $item->checked_at = $isChecked ? now() : null;
        $item->save();

        // Si se marca como comprado, ingresar al inventario
        if ($isChecked && $item->product_id) {
            $meta = $item->metadata ?? [];
            if (empty($meta['inventory_recorded_at'])) {
                try {
                    $qtyBase = (float) ($item->qty_to_buy_base ?? 0);
                    FoodStockBatch::create([
                        'user_id' => $request->user()->id,
                        'product_id' => $item->product_id,
                        'location_id' => $item->location_id,
                        'qty_base' => $qtyBase,
                        'qty_remaining_base' => $qtyBase,
                        'unit_base' => $item->unit_base,
                        'entered_on' => now()->toDateString(),
                    ]);

                    $meta['inventory_recorded_at'] = now()->toISOString();
                    $item->metadata = $meta;
                    $item->save();
                } catch (\Exception $e) {
                    \Log::error('Error al registrar inventario desde toggle:', ['error' => $e->getMessage()]);
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'is_checked' => $item->is_checked]);
        }

        return back();
    }

    /**
     * Actualizar cantidad de un ítem
     */
    public function updateQuantity(Request $request, ShoppingList $list, int $itemId)
    {
        $this->authorizeList($request, $list);

        $item = $list->items()->where('id', $itemId)->firstOrFail();

        $data = $request->validate([
            'qty_to_buy_base' => 'required|numeric|min:0',
        ]);

        $item->qty_to_buy_base = (float) $data['qty_to_buy_base'];
        $item->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'qty_to_buy_base' => $item->qty_to_buy_base]);
        }

        return back();
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

    public function destroyItem(Request $request, ShoppingList $list, ShoppingListItem $item): RedirectResponse|JsonResponse
    {
        $this->authorizeList($request, $list, 'manage');
        abort_unless($item->shopping_list_id === $list->id, 404);
        $item->delete();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

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

    public function destroy(Request $request, ShoppingList $list): RedirectResponse|JsonResponse
    {
        $this->authorizeList($request, $list, 'manage');

        $list->delete();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('status', 'Lista eliminada.');
    }

    public function export(Request $request, ShoppingList $list)
    {
        $this->authorizeList($request, $list);

        $format = strtolower((string) $request->query('format', 'csv'));
        if ($format !== 'json') {
            return $this->exportCsv($request, $list);
        }

        $list->load([
            'items' => fn($q) => $q->orderBy('sort_order'),
            'items.product:id,name,barcode',
            'items.location:id,name',
            'items.category:id,name',
        ]);

        $payload = [
            'type' => 'sogar.shopping-list',
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'list' => [
                'name' => $list->name,
                'list_type' => $list->list_type,
                'expected_purchase_on' => $list->expected_purchase_on?->toDateString(),
                'people_count' => (int) ($list->people_count ?? 0),
                'purchase_frequency_days' => (int) ($list->purchase_frequency_days ?? 0),
                'safety_factor' => (float) ($list->safety_factor ?? 0),
                'estimated_budget' => (float) ($list->estimated_budget ?? 0),
                'meta' => $list->meta,
            ],
            'items' => $list->items->map(function (ShoppingListItem $item) {
                return [
                    'name' => $item->name,
                    'priority' => $item->priority,
                    'qty_to_buy_base' => (float) ($item->qty_to_buy_base ?? 0),
                    'qty_suggested_base' => (float) ($item->qty_suggested_base ?? 0),
                    'qty_current_base' => (float) ($item->qty_current_base ?? 0),
                    'qty_unit_label' => $item->qty_unit_label,
                    'unit_base' => $item->unit_base,
                    'unit_size' => $item->unit_size !== null ? (float) $item->unit_size : null,
                    'estimated_price' => $item->estimated_price !== null ? (float) $item->estimated_price : null,
                    'actual_price' => $item->actual_price !== null ? (float) $item->actual_price : null,
                    'vendor_name' => $item->vendor_name,
                    'is_checked' => (bool) $item->is_checked,
                    'checked_at' => $item->checked_at?->toIso8601String(),
                    'barcode' => $item->barcode,
                    'sort_order' => (int) ($item->sort_order ?? 0),
                    'metadata' => $item->metadata,
                    'product_name' => $item->product?->name,
                    'product_barcode' => $item->product?->barcode,
                    'location_name' => $item->location?->name,
                    'category_name' => $item->category?->name,
                ];
            })->values()->all(),
        ];

        $baseName = $list->name ? Str::slug($list->name) : 'lista';
        $fileName = "shopping-list-{$list->id}-{$baseName}.json";

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $fileName, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    public function exportCsv(Request $request, ShoppingList $list)
    {
        $this->authorizeList($request, $list);

        $list->load([
            'items' => fn($q) => $q->orderBy('sort_order'),
            'items.product:id,name,barcode',
            'items.location:id,name',
            'items.category:id,name',
        ]);

        $baseName = $list->name ? Str::slug($list->name) : 'lista';
        $fileName = "shopping-list-{$list->id}-{$baseName}.csv";

        $headers = [
            'list_id',
            'list_name',
            'list_type',
            'expected_purchase_on',
            'people_count',
            'purchase_frequency_days',
            'safety_factor',
            'estimated_budget',
            'item_name',
            'priority',
            'qty_to_buy_base',
            'qty_suggested_base',
            'qty_current_base',
            'qty_unit_label',
            'unit_base',
            'unit_size',
            'barcode',
            'product_barcode',
            'location_name',
            'category_name',
            'estimated_price',
            'actual_price',
            'vendor_name',
            'is_checked',
            'checked_at',
            'sort_order',
        ];

        $rows = [];
        if ($list->items->count() === 0) {
            $rows[] = [
                (string) $list->id,
                $list->name,
                $list->list_type,
                $list->expected_purchase_on?->toDateString(),
                (int) ($list->people_count ?? 0),
                (int) ($list->purchase_frequency_days ?? 0),
                (float) ($list->safety_factor ?? 0),
                (float) ($list->estimated_budget ?? 0),
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ];
        } else {
            foreach ($list->items as $item) {
                $itemBarcode = $this->formatSpreadsheetCode($item->barcode);
                $productBarcode = $this->formatSpreadsheetCode($item->product?->barcode);

                $rows[] = [
                    (string) $list->id,
                    $list->name,
                    $list->list_type,
                    $list->expected_purchase_on?->toDateString(),
                    (int) ($list->people_count ?? 0),
                    (int) ($list->purchase_frequency_days ?? 0),
                    (float) ($list->safety_factor ?? 0),
                    (float) ($list->estimated_budget ?? 0),
                    $item->name,
                    $item->priority,
                    (string) ($item->qty_to_buy_base ?? ''),
                    (string) ($item->qty_suggested_base ?? ''),
                    (string) ($item->qty_current_base ?? ''),
                    $item->qty_unit_label,
                    $item->unit_base,
                    $item->unit_size !== null ? (string) $item->unit_size : '',
                    $itemBarcode,
                    $productBarcode,
                    $item->location?->name,
                    $item->category?->name,
                    $item->estimated_price !== null ? (string) $item->estimated_price : '',
                    $item->actual_price !== null ? (string) $item->actual_price : '',
                    $item->vendor_name,
                    $item->is_checked ? '1' : '0',
                    $item->checked_at?->toIso8601String(),
                    (string) ($item->sort_order ?? 0),
                ];
            }
        }

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM para que Excel/Sheets detecten correctamente.
            fwrite($out, "\xEF\xBB\xBF");
            // En muchas configuraciones regionales (ES/LatAm) Excel usa ';' como separador.
            $delimiter = ';';
            fputcsv($out, $headers, $delimiter);
            foreach ($rows as $row) {
                fputcsv($out, $row, $delimiter);
            }
            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function templateCsv(Request $request)
    {
        $fileName = 'shopping-list-template.csv';

        $callback = function () {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fwrite($out, "\xEF\xBB\xBF");

            // Formato mínimo
            fputcsv($out, ['producto', 'cantidad'], ';');
            fputcsv($out, ['Arroz', '2'], ';');

            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        ShoppingListType::ensureDefaultsForUser($request->user()->id);

        $request->validate([
            'file' => 'required|file|max:4096|mimetypes:application/json,text/plain,application/octet-stream,text/csv,application/csv,application/vnd.ms-excel',
        ]);

        $raw = file_get_contents($request->file('file')->getRealPath());
        $trimmed = ltrim((string) $raw);
        $looksJson = str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[');

        if (!$looksJson) {
            return $this->importFromCsv($request);
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            // Si no es JSON válido, intentamos CSV como fallback
            return $this->importFromCsv($request);
        }

        if (!is_array($decoded) || ($decoded['type'] ?? null) !== 'sogar.shopping-list') {
            return back()->withErrors(['file' => 'Archivo inválido. Usa un .csv (Google Sheets) o un .json exportado por SOGAR.']);
        }

        $version = (int) ($decoded['version'] ?? 0);
        if ($version !== 1) {
            return back()->withErrors(['file' => 'Versión de archivo no soportada.']);
        }

        $listData = $decoded['list'] ?? null;
        $itemsData = $decoded['items'] ?? [];

        if (!is_array($listData) || !is_array($itemsData)) {
            return back()->withErrors(['file' => 'Estructura de archivo inválida.']);
        }

        $userId = $request->user()->id;

        $listType = (string) ($listData['list_type'] ?? 'general');
        $listTypeAllowed = ShoppingListType::where('user_id', $userId)
            ->where('slug', $listType)
            ->where('is_active', true)
            ->exists();
        if (!$listTypeAllowed) {
            $listType = 'general';
        }

        $listName = trim((string) ($listData['name'] ?? ''));
        if ($listName === '') {
            $listName = 'Lista importada - ' . now()->locale('es')->translatedFormat('j M');
        }

        $expectedPurchaseOn = null;
        if (!empty($listData['expected_purchase_on'])) {
            try {
                $expectedPurchaseOn = Carbon::parse((string) $listData['expected_purchase_on'])->toDateString();
            } catch (\Throwable) {
                $expectedPurchaseOn = null;
            }
        }

        $exportedAt = $decoded['exported_at'] ?? null;

        $newList = DB::transaction(function () use ($request, $userId, $listName, $listType, $expectedPurchaseOn, $listData, $itemsData, $exportedAt) {
            $list = ShoppingList::create([
                'user_id' => $userId,
                'family_group_id' => $request->user()->active_family_group_id,
                'name' => $listName,
                'list_type' => $listType,
                'status' => 'active',
                'generated_at' => now(),
                'expected_purchase_on' => $expectedPurchaseOn,
                'people_count' => (int) ($listData['people_count'] ?? 3),
                'purchase_frequency_days' => (int) ($listData['purchase_frequency_days'] ?? 7),
                'safety_factor' => (float) ($listData['safety_factor'] ?? 1.2),
                'estimated_budget' => (float) ($listData['estimated_budget'] ?? 0),
                'meta' => array_merge((array) ($listData['meta'] ?? []), [
                    'import' => [
                        'exported_at' => $exportedAt,
                        'imported_at' => now()->toIso8601String(),
                    ],
                ]),
            ]);

            $importedCount = 0;

            foreach ($itemsData as $idx => $itemData) {
                if (!is_array($itemData)) {
                    continue;
                }

                $name = trim((string) ($itemData['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $productId = null;
                $candidateBarcode = $itemData['product_barcode'] ?? $itemData['barcode'] ?? null;
                if (is_string($candidateBarcode) && trim($candidateBarcode) !== '') {
                    $productId = FoodProduct::where('user_id', $userId)
                        ->where('barcode', trim($candidateBarcode))
                        ->value('id');
                }

                $locationId = null;
                if (!empty($itemData['location_name']) && is_string($itemData['location_name'])) {
                    $locationId = FoodLocation::where('user_id', $userId)
                        ->where('name', $itemData['location_name'])
                        ->value('id');
                }

                $categoryId = null;
                if (!empty($itemData['category_name']) && is_string($itemData['category_name'])) {
                    $categoryId = Category::where('user_id', $userId)
                        ->where('name', $itemData['category_name'])
                        ->value('id');
                }

                $checkedAt = null;
                if (!empty($itemData['checked_at']) && is_string($itemData['checked_at'])) {
                    try {
                        $checkedAt = Carbon::parse($itemData['checked_at']);
                    } catch (\Throwable) {
                        $checkedAt = null;
                    }
                }

                ShoppingListItem::create([
                    'shopping_list_id' => $list->id,
                    'product_id' => $productId,
                    'category_id' => $categoryId,
                    'location_id' => $locationId,
                    'name' => $name,
                    'priority' => (string) ($itemData['priority'] ?? 'medium'),
                    'qty_suggested_base' => (float) ($itemData['qty_suggested_base'] ?? 0),
                    'qty_current_base' => (float) ($itemData['qty_current_base'] ?? 0),
                    'qty_to_buy_base' => (float) ($itemData['qty_to_buy_base'] ?? 0),
                    'qty_unit_label' => $itemData['qty_unit_label'] ?? null,
                    'unit_base' => $itemData['unit_base'] ?? null,
                    'unit_size' => isset($itemData['unit_size']) ? (float) $itemData['unit_size'] : null,
                    'estimated_price' => isset($itemData['estimated_price']) ? (float) $itemData['estimated_price'] : 0,
                    'actual_price' => isset($itemData['actual_price']) ? (float) $itemData['actual_price'] : null,
                    'vendor_name' => $itemData['vendor_name'] ?? null,
                    'is_checked' => (bool) ($itemData['is_checked'] ?? false),
                    'checked_at' => $checkedAt,
                    'barcode' => $itemData['barcode'] ?? null,
                    'sort_order' => (int) ($itemData['sort_order'] ?? $idx),
                    'metadata' => $itemData['metadata'] ?? null,
                ]);

                $importedCount++;
            }

            $this->logListEvent($list, 'list_imported', [
                'items_imported' => $importedCount,
                'source_exported_at' => $exportedAt,
            ]);

            return $list;
        });

        return redirect()->route('food.shopping-list.show', $newList)
            ->with('status', 'Lista importada correctamente.');
    }

    private function importFromCsv(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;
        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->withErrors(['file' => 'No se pudo leer el archivo.']);
        }

        // Detectar delimitador (Excel ES suele usar ';')
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return back()->withErrors(['file' => 'CSV vacío o inválido.']);
        }
        $semicolonCount = substr_count($firstLine, ';');
        $commaCount = substr_count($firstLine, ',');
        $tabCount = substr_count($firstLine, "\t");
        $delimiter = ',';
        if ($tabCount > $semicolonCount && $tabCount > $commaCount) {
            $delimiter = "\t";
        } elseif ($semicolonCount > $commaCount) {
            $delimiter = ';';
        }

        rewind($handle);
        $firstRow = fgetcsv($handle, 0, $delimiter);
        if (!$firstRow || !is_array($firstRow)) {
            fclose($handle);
            return back()->withErrors(['file' => 'CSV vacío o inválido.']);
        }

        // Quitar BOM si existe
        if (isset($firstRow[0])) {
            $firstRow[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $firstRow[0]);
        }

        $headers = array_map(fn($h) => trim((string) $h), $firstRow);

        $normalizeHeader = function (string $header): string {
            $header = trim(mb_strtolower($header));
            $header = str_replace([' ', '-', '.', ':'], '_', $header);
            $header = preg_replace('/_+/', '_', $header);
            $header = trim($header, '_');
            // quitar acentos básicos
            $header = strtr($header, [
                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
                'ñ' => 'n',
            ]);
            return $header;
        };

        $normalized = [];
        foreach ($headers as $idx => $header) {
            $normalized[$normalizeHeader($header)] = $idx;
        }

        $findIndex = function (array $aliases) use ($normalized, $normalizeHeader): ?int {
            foreach ($aliases as $alias) {
                $key = $normalizeHeader((string) $alias);
                if (array_key_exists($key, $normalized)) {
                    return $normalized[$key];
                }
            }
            return null;
        };

        // Mapeo canónico de columnas (soporta headers ES / EN)
        $col = [
            'list_id' => $findIndex(['list_id', 'id_lista', 'lista_id']),
            'list_name' => $findIndex(['list_name', 'lista', 'nombre_lista', 'list']),
            'list_type' => $findIndex(['list_type', 'tipo', 'type']),
            'expected_purchase_on' => $findIndex(['expected_purchase_on', 'fecha', 'fecha_estimada', 'expected_date']),
            'people_count' => $findIndex(['people_count', 'personas', 'people']),
            'purchase_frequency_days' => $findIndex(['purchase_frequency_days', 'frecuencia_dias', 'frecuencia', 'frequency_days']),
            'safety_factor' => $findIndex(['safety_factor', 'factor', 'safety']),
            'estimated_budget' => $findIndex(['estimated_budget', 'presupuesto', 'budget']),
            'item_name' => $findIndex(['item_name', 'producto', 'product', 'nombre', 'name', 'item']),
            'priority' => $findIndex(['priority', 'prioridad']),
            'qty_to_buy_base' => $findIndex(['qty_to_buy_base', 'cantidad', 'qty', 'cantidad_a_comprar']),
            'qty_suggested_base' => $findIndex(['qty_suggested_base', 'qty_suggested', 'cantidad_sugerida']),
            'qty_current_base' => $findIndex(['qty_current_base', 'qty_current', 'cantidad_actual']),
            'qty_unit_label' => $findIndex(['qty_unit_label', 'unidad', 'unit_label']),
            'unit_base' => $findIndex(['unit_base', 'unidad_base', 'unit_base_label']),
            'unit_size' => $findIndex(['unit_size', 'tamano', 'tamaño', 'size']),
            'product_barcode' => $findIndex(['product_barcode', 'barcode', 'codigo', 'codigo_barras', 'ean', 'upc']),
            'barcode' => $findIndex(['barcode', 'codigo', 'codigo_barras', 'ean', 'upc']),
            'location_name' => $findIndex(['location_name', 'ubicacion', 'ubicacion_nombre', 'location']),
            'category_name' => $findIndex(['category_name', 'categoria', 'category']),
            'estimated_price' => $findIndex(['estimated_price', 'precio_estimado', 'estimated']),
            'actual_price' => $findIndex(['actual_price', 'precio_real', 'actual']),
            'vendor_name' => $findIndex(['vendor_name', 'proveedor', 'tienda', 'vendor']),
            'is_checked' => $findIndex(['is_checked', 'comprado', 'checked']),
            'checked_at' => $findIndex(['checked_at', 'fecha_compra', 'checked_date']),
            'sort_order' => $findIndex(['sort_order', 'orden', 'sort']),
        ];

        $isFullFormat = $col['list_name'] !== null && $col['list_type'] !== null;

        // En formato simplificado, lo único obligatorio es el producto.
        if ($col['item_name'] === null) {
            fclose($handle);
            return back()->withErrors(['file' => 'CSV inválido: falta la columna del producto (usa "producto" o "item_name").']);
        }

        $rows = [];
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (!is_array($data)) continue;
            // Ignorar filas completamente vacías
            $nonEmpty = false;
            foreach ($data as $v) {
                if (trim((string) $v) !== '') { $nonEmpty = true; break; }
            }
            if (!$nonEmpty) continue;

            $rows[] = $data;
        }
        fclose($handle);

        if (count($rows) === 0) {
            return back()->withErrors(['file' => 'CSV sin filas de datos.']);
        }

        $get = function(array $row, string $key) use ($col) {
            $i = $col[$key] ?? null;
            if ($i === null) return null;
            return array_key_exists($i, $row) ? $row[$i] : null;
        };

        $first = $rows[0];

        $targetList = null;
        $targetListId = (int) trim((string) ($get($first, 'list_id') ?? 0));
        if ($targetListId > 0) {
            $targetList = ShoppingList::find($targetListId);
            if (!$targetList) {
                return back()->withErrors(['file' => 'La lista indicada en el CSV (list_id) no existe o ya fue eliminada.']);
            }
            try {
                $this->authorizeList($request, $targetList, 'manage');
            } catch (\Throwable) {
                return back()->withErrors(['file' => 'No tienes permiso para actualizar esa lista (list_id).']);
            }
        }

        $listType = trim((string) ($get($first, 'list_type') ?? 'general'));
        $listTypeAllowed = ShoppingListType::where('user_id', $userId)
            ->where('slug', $listType)
            ->where('is_active', true)
            ->exists();
        if (!$listTypeAllowed) {
            $listType = 'general';
        }

        $listName = trim((string) ($get($first, 'list_name') ?? ''));
        if ($listName === '') {
            $listName = 'Lista importada - ' . now()->locale('es')->translatedFormat('j M');
        }

        $expectedPurchaseOn = null;
        $expectedRaw = $get($first, 'expected_purchase_on');
        if (is_string($expectedRaw) && trim($expectedRaw) !== '') {
            try {
                $expectedPurchaseOn = Carbon::parse($expectedRaw)->toDateString();
            } catch (\Throwable) {
                $expectedPurchaseOn = null;
            }
        }

        $parseBool = function($value): bool {
            $v = strtolower(trim((string) $value));
            return in_array($v, ['1', 'true', 'yes', 'si', 'sí'], true);
        };

        $parseFloat = function($value): float {
            $v = trim((string) $value);
            if ($v === '') return 0.0;
            // soporte coma decimal
            $v = str_replace(['.', ','], ['.', '.'], $v);
            return (float) $v;
        };

        $parseInt = function($value): int {
            $v = trim((string) $value);
            if ($v === '') return 0;
            return (int) $v;
        };

        $newList = DB::transaction(function () use ($request, $userId, $rows, $get, $listName, $listType, $expectedPurchaseOn, $parseBool, $parseFloat, $parseInt, $isFullFormat, $targetList) {
            if ($targetList) {
                $list = $targetList->fresh();

                $meta = is_array($list->meta ?? null) ? $list->meta : [];
                $meta['import'] = [
                    'source' => 'csv',
                    'format' => $isFullFormat ? 'full' : 'simple',
                    'mode' => 'update',
                    'imported_at' => now()->toIso8601String(),
                ];

                $list->fill([
                    'name' => $listName,
                    'list_type' => $listType,
                    'expected_purchase_on' => $expectedPurchaseOn,
                    // En CSV simplificado, estos campos suelen no venir
                    'people_count' => $parseInt($get($rows[0], 'people_count') ?? ($list->people_count ?? 3)),
                    'purchase_frequency_days' => $parseInt($get($rows[0], 'purchase_frequency_days') ?? ($list->purchase_frequency_days ?? 7)),
                    'safety_factor' => $parseFloat($get($rows[0], 'safety_factor') ?? ($list->safety_factor ?? 1.2)),
                    'estimated_budget' => $parseFloat($get($rows[0], 'estimated_budget') ?? ($list->estimated_budget ?? 0)),
                    'meta' => $meta,
                ]);
                $list->save();

                // Evitar duplicados: reemplazar items por el contenido del CSV
                $list->items()->delete();
            } else {
                $list = ShoppingList::create([
                    'user_id' => $userId,
                    'family_group_id' => $request->user()->active_family_group_id,
                    'name' => $listName,
                    'list_type' => $listType,
                    'status' => 'active',
                    'generated_at' => now(),
                    'expected_purchase_on' => $expectedPurchaseOn,
                    // En CSV simplificado, estos campos suelen no venir
                    'people_count' => $parseInt($get($rows[0], 'people_count') ?? 3),
                    'purchase_frequency_days' => $parseInt($get($rows[0], 'purchase_frequency_days') ?? 7),
                    'safety_factor' => $parseFloat($get($rows[0], 'safety_factor') ?? 1.2),
                    'estimated_budget' => $parseFloat($get($rows[0], 'estimated_budget') ?? 0),
                    'meta' => [
                        'import' => [
                            'source' => 'csv',
                            'format' => $isFullFormat ? 'full' : 'simple',
                            'mode' => 'create',
                            'imported_at' => now()->toIso8601String(),
                        ],
                    ],
                ]);
            }

            $importedCount = 0;

            foreach ($rows as $idx => $row) {
                $itemName = trim((string) ($get($row, 'item_name') ?? ''));
                if ($itemName === '') {
                    continue;
                }

                $productId = null;
                $candidateBarcode = $this->normalizeSpreadsheetCode($get($row, 'product_barcode') ?? $get($row, 'barcode'));
                if ($candidateBarcode !== '') {
                    $productId = FoodProduct::where('user_id', $userId)
                    ->where('barcode', $candidateBarcode)
                        ->value('id');
                }

                $locationId = null;
                $locName = $get($row, 'location_name');
                if (is_string($locName) && trim($locName) !== '') {
                    $locationId = FoodLocation::where('user_id', $userId)
                        ->where('name', trim($locName))
                        ->value('id');
                }

                $categoryId = null;
                $catName = $get($row, 'category_name');
                if (is_string($catName) && trim($catName) !== '') {
                    $categoryId = Category::where('user_id', $userId)
                        ->where('name', trim($catName))
                        ->value('id');
                }

                $checkedAt = null;
                $checkedAtRaw = $get($row, 'checked_at');
                if (is_string($checkedAtRaw) && trim($checkedAtRaw) !== '') {
                    try {
                        $checkedAt = Carbon::parse($checkedAtRaw);
                    } catch (\Throwable) {
                        $checkedAt = null;
                    }
                }

                ShoppingListItem::create([
                    'shopping_list_id' => $list->id,
                    'product_id' => $productId,
                    'category_id' => $categoryId,
                    'location_id' => $locationId,
                    'name' => $itemName,
                    'priority' => (string) ($get($row, 'priority') ?? 'medium'),
                    // En CSV simplificado, la cantidad puede venir vacía: default 1
                    'qty_to_buy_base' => ($q = $parseFloat($get($row, 'qty_to_buy_base') ?? 1)) > 0 ? $q : 1,
                    'qty_suggested_base' => $parseFloat($get($row, 'qty_suggested_base') ?? 0),
                    'qty_current_base' => $parseFloat($get($row, 'qty_current_base') ?? 0),
                    'qty_unit_label' => $get($row, 'qty_unit_label') ?: null,
                    'unit_base' => $get($row, 'unit_base') ?: null,
                    'unit_size' => (($u = $get($row, 'unit_size')) !== null && trim((string)$u) !== '') ? (float) $u : null,
                    'barcode' => ($b = $this->normalizeSpreadsheetCode($get($row, 'barcode') ?? '')) !== '' ? $b : null,
                    'estimated_price' => $parseFloat($get($row, 'estimated_price') ?? 0),
                    'actual_price' => (($a = $get($row, 'actual_price')) !== null && trim((string)$a) !== '') ? (float) $a : null,
                    'vendor_name' => $get($row, 'vendor_name') ?: null,
                    'is_checked' => $parseBool($get($row, 'is_checked') ?? false),
                    'checked_at' => $checkedAt,
                    'sort_order' => $parseInt($get($row, 'sort_order') ?? $idx),
                ]);

                $importedCount++;
            }

            $this->logListEvent($list, 'list_imported', [
                'items_imported' => $importedCount,
                'source' => 'csv',
                'mode' => $targetList ? 'update' : 'create',
            ]);

            return $list;
        });

        return redirect()->route('food.shopping-list.show', $newList)
            ->with('status', 'Lista importada correctamente (CSV).');
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

        $list->load(['items' => function($query) {
            $query->with('product')->orderBy('created_at', 'desc');
        }]);

        $products = \App\Models\FoodProduct::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'barcode']);

        $foodTypes = \App\Models\FoodType::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        return view('food.shopping-list.show', [
            'list' => $list,
            'products' => $products,
            'foodTypes' => $foodTypes,
        ]);
    }

    private function formatSpreadsheetCode(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        // Evita notación científica en Excel/Sheets para códigos largos (EAN/UPC)
        if (preg_match('/^\d{10,}$/', $value)) {
            return '="' . $value . '"';
        }

        return $value;
    }

    private function normalizeSpreadsheetCode(mixed $value): string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return '';
        }

        // Limpiar separadores comunes
        $v = preg_replace('/[\s\-]+/', '', $v);

        // Sheets/Excel pueden exportar texto forzado como: ="7702..."
        if (preg_match('/^\s*=\s*"(.*)"\s*$/', $v, $m)) {
            $v = $m[1];
        }

        // También puede venir con apóstrofe de "texto": '7702...
        if (str_starts_with($v, "'")) {
            $v = substr($v, 1);
        }

        $v = trim($v);

        // Notación científica (p.ej. 7,702E+12)
        if (preg_match('/^[0-9]+([\.,][0-9]+)?[eE][\+\-]?[0-9]+$/', $v)) {
            $expanded = $this->expandScientificNotation($v);
            $expanded = str_replace(['.', '+', '-'], '', $expanded);
            $v = $expanded;
        }

        return $v;
    }

    private function expandScientificNotation(string $value): string
    {
        $value = trim($value);
        $value = str_replace(',', '.', $value);

        if (!preg_match('/^([\+\-]?)(\d+)(?:\.(\d+))?[eE]([\+\-]?\d+)$/', $value, $m)) {
            return $value;
        }

        $sign = $m[1] ?? '';
        $int = $m[2] ?? '0';
        $frac = $m[3] ?? '';
        $exp = (int) ($m[4] ?? 0);

        $digits = $int . $frac;
        $decPlaces = strlen($frac);
        $shift = $exp - $decPlaces;

        if ($shift >= 0) {
            $plain = $digits . str_repeat('0', $shift);
        } else {
            $pos = strlen($digits) + $shift;
            if ($pos <= 0) {
                $plain = '0.' . str_repeat('0', -$pos) . $digits;
            } else {
                $plain = substr($digits, 0, $pos) . '.' . substr($digits, $pos);
            }
        }

        return $sign === '-' ? '-' . $plain : $plain;
    }

    private function logListEvent(ShoppingList $list, string $event, array $payload = []): void
    {
        app(ShoppingListEventLogger::class)->log($list, $event, $payload);
    }
}
