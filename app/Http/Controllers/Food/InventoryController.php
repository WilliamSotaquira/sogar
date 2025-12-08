<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\FoodLocation;
use App\Models\FoodProduct;
use App\Models\FoodStockBatch;
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

        return view('food.inventory.index', [
            'batches' => $batches,
            'products' => $products,
            'locations' => $locations,
            'types' => $types,
            'activeLocation' => $activeLocation,
            'activeType' => $activeType,
            'filterHistory' => $history,
        ]);
    }
}
