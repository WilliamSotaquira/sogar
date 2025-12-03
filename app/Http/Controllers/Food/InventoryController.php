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

        return view('food.inventory.index', [
            'batches' => $batches,
            'products' => $products,
            'locations' => $locations,
            'types' => $products->pluck('type')->filter()->unique('id'),
        ]);
    }
}
