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
        $products = FoodProduct::with(['type', 'defaultLocation', 'batches' => function ($query) {
                $query->where('status', 'ok');
            }])
            ->where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        // Calcular stock actual y precio actual para cada producto
        $products->each(function ($product) {
            $product->current_stock = $product->batches->sum('qty_remaining_base');

            // Obtener el precio más reciente
            $latestPrice = \App\Models\FoodPrice::where('product_id', $product->id)
                ->orderBy('captured_on', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            $product->current_price = $latestPrice ? $latestPrice->price_per_base : null;
            $product->current_vendor = $latestPrice ? $latestPrice->vendor : null;
        });

        return view('food.products.index', [
            'products' => $products,
            'types' => FoodType::withCount('products')->where('user_id', $request->user()->id)->where('is_active', true)->orderBy('sort_order')->get(),
            'locations' => FoodLocation::withCount('products')->where('user_id', $request->user()->id)->orderBy('sort_order')->get(),
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
            'unit_size' => 'required|numeric|min:0.001',
            'min_stock_qty' => 'nullable|numeric|min:0',
            'shelf_life_days' => 'nullable|integer|min:1|max:3650',
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('sogar_food_products', 'barcode')
                    ->where('user_id', $request->user()->id)
                    ->ignore(null)
            ],
            'image_url' => 'nullable|string|max:500',
            'initial_price' => 'nullable|numeric|min:0',
            'initial_vendor' => 'nullable|string|max:255',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['min_stock_qty'] = $data['min_stock_qty'] ?? 1;
        $data['presentation_qty'] = 1;

        $product = FoodProduct::create($data);

        // Guardar precio inicial si fue proporcionado
        if (!empty($data['initial_price']) && $data['initial_price'] > 0) {
            $priceService = app(\App\Services\PriceChangeService::class);
            $priceService->registerPriceChange(
                $product,  // Pasar el objeto completo, no el id
                $data['initial_price'],
                $data['initial_vendor'] ?? null,
                'initial',
                'Precio inicial al crear el producto'
            );
        }

        return back()->with('status', 'Producto guardado correctamente.');
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
            'presentation_qty' => 'nullable|numeric|min:0',
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
