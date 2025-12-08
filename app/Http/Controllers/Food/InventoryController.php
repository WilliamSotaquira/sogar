<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\FoodLocation;
use App\Models\FoodProduct;
use App\Models\FoodStockBatch;
use App\Models\ShoppingListItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $locationId = $request->input('location_id');
        $typeId = $request->input('type_id');

        $batches = FoodStockBatch::with(['product.type', 'location'])
            ->where('user_id', $userId)
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->when($typeId, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('type_id', $typeId)))
            ->orderBy('expires_on')
            ->get();

        $products = FoodProduct::where('user_id', $userId)->orderBy('name')->get();
        $locations = FoodLocation::where('user_id', $userId)->orderBy('sort_order')->get();
        $types = $products->pluck('type')->filter()->unique('id')->values();

        $activeLocation = $locations->firstWhere('id', (int) $locationId);
        $activeType = $types->firstWhere('id', (int) $typeId);

        $history = collect(session('inventory_filters_history', []));
        if ($activeLocation || $activeType) {
            $entry = [
                'location_id' => $activeLocation?->id,
                'type_id' => $activeType?->id,
                'label' => trim(($activeLocation?->name ?? 'Todas las ubicaciones') . ($activeType ? ' · ' . $activeType->name : '')),
            ];

            $history = $history->reject(fn ($item) => $item['location_id'] === $entry['location_id'] && $item['type_id'] === $entry['type_id'])
                ->prepend($entry)
                ->take(5);

            session(['inventory_filters_history' => $history->values()->all()]);
        }

        $user = $request->user();
        $familyGroupIds = method_exists($user, 'familyGroupIds') ? $user->familyGroupIds() : [];

        $pendingInventoryPool = ShoppingListItem::with(['list', 'product.defaultLocation', 'location'])
            ->where('is_checked', true)
            ->whereHas('list', function ($query) use ($user, $familyGroupIds) {
                $query->where(function ($inner) use ($user, $familyGroupIds) {
                    $inner->where('user_id', $user->id);

                    if (!empty($familyGroupIds)) {
                        $inner->orWhereIn('family_group_id', $familyGroupIds);
                    }
                });
            })
            ->latest('checked_at')
            ->take(30)
            ->get()
            ->filter(fn ($item) => empty(data_get($item->metadata, 'inventory_batch_id')))
            ->values();

        $pendingListFilterId = (int) $request->input('pending_list_id');

        $pendingInventoryItems = $pendingInventoryPool;
        if ($pendingListFilterId) {
            $pendingInventoryItems = $pendingInventoryPool
                ->where('shopping_list_id', $pendingListFilterId)
                ->values();
        }

        $pendingInventoryFilterOptions = $pendingInventoryPool
            ->pluck('list')
            ->filter()
            ->unique('id')
            ->values();

        return view('food.inventory.index', [
            'batches' => $batches,
            'products' => $products,
            'locations' => $locations,
            'types' => $types,
            'activeLocation' => $activeLocation,
            'activeType' => $activeType,
            'filterHistory' => $history,
            'pendingInventoryItems' => $pendingInventoryItems,
            'pendingInventoryCount' => $pendingInventoryPool->count(),
            'pendingInventoryFilterOptions' => $pendingInventoryFilterOptions,
            'activePendingListId' => $pendingListFilterId,
        ]);
    }
}
