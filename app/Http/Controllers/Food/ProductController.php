<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\FoodLocation;
use App\Models\FoodPrice;
use App\Models\FoodProduct;
use App\Models\FoodStockBatch;
use App\Models\FoodStockMovement;
use App\Models\FoodType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = FoodProduct::with('type')
            ->where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get()
            ->map(function (FoodProduct $product) {
                $latestPrice = FoodPrice::where('product_id', $product->id)
                    ->orderBy('captured_on', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();

                $product->current_price = $latestPrice;
                $product->presentation_label = $this->formatPresentation($product);
                return $product;
            });

        return view('food.products.index', [
            'products' => $products,
        ]);
    }

    public function create(Request $request): View
    {
        return view('food.products.create', [
            'types' => FoodType::withCount('products')
                ->where('user_id', $request->user()->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'locations' => FoodLocation::withCount('products')
                ->where('user_id', $request->user()->id)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
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
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('sogar_food_products', 'barcode')
                    ->where('user_id', $request->user()->id)
                    ->ignore(null)
            ],
            'image_url' => 'nullable|string|max:500',
            'initial_price' => 'nullable|numeric|min:0',
            'initial_vendor' => 'nullable|string|max:255',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['min_stock_qty'] = $data['min_stock_qty'] ?? 1;
        $data['unit_size'] = $data['unit_size'] ?? 1;
        $data['presentation_qty'] = $data['presentation_qty'] ?? ($data['unit_size'] ?? 1);

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

        // Si es una petición JSON, devolver respuesta JSON
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Producto creado correctamente',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                ]
            ], 201);
        }

        return redirect()
            ->route('food.products.show', $product)
            ->with('status', 'Producto guardado correctamente.');
    }

    public function quickStore(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'brand' => 'nullable|string|max:255',
                'type_id' => 'nullable|exists:sogar_food_types,id',
                'barcode' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('sogar_food_products', 'barcode')
                        ->where('user_id', $request->user()->id)
                        ->whereNotNull('barcode')
                ],
                'add_to_inventory' => 'nullable|boolean',
                'inventory_qty' => 'nullable|numeric|min:0.1',
                'unit_base' => 'nullable|string|max:16',
                'location_id' => 'nullable|exists:sogar_food_locations,id',
                'expiry_date' => 'nullable|date|after_or_equal:today',
            ]);

            $data['user_id'] = $request->user()->id;
            $data['unit_base'] = $data['unit_base'] ?? 'unit';
            $data['unit_size'] = 1;
            $data['min_stock_qty'] = 1;
            $data['presentation_qty'] = 1;

            // Limpiar barcode vacío
            if (empty($data['barcode'])) {
                unset($data['barcode']);
            }

            $product = FoodProduct::create($data);

            // Si se marcó agregar a inventario
            if ($request->boolean('add_to_inventory') && $request->filled('inventory_qty')) {
                $batchData = [
                    'product_id' => $product->id,
                    'location_id' => $request->input('location_id'),
                    'qty_purchased_base' => $request->input('inventory_qty'),
                    'qty_remaining_base' => $request->input('inventory_qty'),
                    'unit_base' => $request->input('unit_base', 'unit'),
                    'status' => 'ok',
                    'purchased_on' => now(),
                ];

                if ($request->filled('expiry_date')) {
                    $batchData['expires_on'] = $request->input('expiry_date');
                }

                FoodStockBatch::create($batchData);

                return response()->json([
                    'success' => true,
                    'message' => 'Producto creado y agregado al inventario',
                    'product' => $product,
                    'redirect' => route('food.inventory.index')
                ], 201);
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto creado correctamente',
                'product' => $product,
                'redirect' => route('food.products.show', $product)
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error en quickStore: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, FoodProduct $product): View
    {
        $this->authorizeProduct($request, $product);

        $product->load(['type', 'defaultLocation', 'barcodes']);

        $currentStock = FoodStockBatch::where('product_id', $product->id)
            ->where('status', 'ok')
            ->sum('qty_remaining_base');

        $openBatches = FoodStockBatch::with('location')
            ->where('product_id', $product->id)
            ->where('status', 'ok')
            ->orderBy('expires_on')
            ->get();

        $expiringSoon = $openBatches
            ->whereNotNull('expires_on')
            ->filter(fn ($batch) => $batch->expires_on <= now()->addDays(7))
            ->count();

        $latestPrice = FoodPrice::where('product_id', $product->id)
            ->orderBy('captured_on', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $recentMovements = FoodStockMovement::where('product_id', $product->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('food.products.show', [
            'product' => $product,
            'currentStock' => $currentStock,
            'expiringSoon' => $expiringSoon,
            'latestPrice' => $latestPrice,
            'recentMovements' => $recentMovements,
            'presentationLabel' => $this->formatPresentation($product),
            'openBatches' => $openBatches,
        ]);
    }

    public function edit(Request $request, FoodProduct $product): View
    {
        $this->authorizeProduct($request, $product);

        return view('food.products.edit', [
            'product' => $product,
            'types' => FoodType::withCount('products')
                ->where('user_id', $request->user()->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'locations' => FoodLocation::withCount('products')
                ->where('user_id', $request->user()->id)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function update(Request $request, FoodProduct $product): RedirectResponse|JsonResponse
    {
        $this->authorizeProduct($request, $product);

        // Si solo se actualiza la ubicación (petición AJAX)
        if ($request->wantsJson() && $request->has('default_location_id') && count($request->all()) <= 2) {
            $request->validate([
                'default_location_id' => 'nullable|exists:sogar_food_locations,id',
            ]);

            $product->update(['default_location_id' => $request->input('default_location_id')]);

            return response()->json([
                'success' => true,
                'message' => 'Producto asignado correctamente',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                ],
            ]);
        }

        // Actualización completa del formulario
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

        $data['is_active'] = $request->boolean('is_active');

        $product->update($data);

        return redirect()
            ->route('food.products.show', $product)
            ->with('status', 'Producto actualizado.');
    }

    public function destroy(Request $request, FoodProduct $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);

        // Eliminar datos relacionados en cascada
        $product->barcodes()->delete();
        $product->batches()->delete();
        $product->movements()->delete();
        $product->prices()->delete();

        // Eliminar el producto
        $product->delete();

        return redirect()
            ->route('food.products.index')
            ->with('status', 'Producto eliminado correctamente junto con sus datos relacionados.');
    }

    private function authorizeProduct(Request $request, FoodProduct $product): void
    {
        abort_unless($product->user_id === $request->user()->id, 403);
    }

    private function formatPresentation(FoodProduct $product): string
    {
        $size = (float) ($product->presentation_qty ?? $product->unit_size ?? 0);
        $unit = $product->unit_base ?: 'unidad';

        if ($size <= 0) {
            return '—';
        }

        $formattedSize = fmod($size, 1) === 0.0 ? number_format($size, 0) : number_format($size, 2);

        $unitLabel = match ($unit) {
            'g' => 'g',
            'kg' => 'kg',
            'ml' => 'ml',
            'l' => 'L',
            'unit' => 'unid.',
            default => $unit,
        };

        return "{$formattedSize} {$unitLabel}";
    }
}
