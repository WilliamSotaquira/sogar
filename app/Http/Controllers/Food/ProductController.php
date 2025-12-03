<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\FoodLocation;
use App\Models\FoodProduct;
use App\Models\FoodType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = FoodProduct::with(['type', 'defaultLocation'])
            ->where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        return view('food.products.index', [
            'products' => $products,
            'types' => FoodType::where('user_id', $request->user()->id)->where('is_active', true)->orderBy('sort_order')->get(),
            'locations' => FoodLocation::where('user_id', $request->user()->id)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'type_id' => 'nullable|exists:sogar_food_types,id',
            'default_location_id' => 'nullable|exists:sogar_food_locations,id',
            'unit_base' => 'required|string|max:16',
            'unit_size' => 'nullable|numeric|min:0.001',
            'min_stock_qty' => 'nullable|numeric|min:0',
            'shelf_life_days' => 'nullable|integer|min:1|max:3650',
            'barcode' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['unit_size'] = $data['unit_size'] ?? 1;

        FoodProduct::create($data);

        return back()->with('status', 'Producto guardado.');
    }

    public function update(Request $request, FoodProduct $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'type_id' => 'nullable|exists:sogar_food_types,id',
            'default_location_id' => 'nullable|exists:sogar_food_locations,id',
            'unit_base' => 'required|string|max:16',
            'unit_size' => 'nullable|numeric|min:0.001',
            'min_stock_qty' => 'nullable|numeric|min:0',
            'shelf_life_days' => 'nullable|integer|min:1|max:3650',
            'barcode' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $product->update($data);

        return back()->with('status', 'Producto actualizado.');
    }

    public function destroy(Request $request, FoodProduct $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);
        $product->delete();
        return back()->with('status', 'Producto eliminado.');
    }

    private function authorizeProduct(Request $request, FoodProduct $product): void
    {
        abort_unless($product->user_id === $request->user()->id, 403);
    }
}
