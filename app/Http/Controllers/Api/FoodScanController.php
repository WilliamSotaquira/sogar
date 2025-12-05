<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FoodBarcode;
use App\Models\FoodProduct;
use App\Services\ExternalProductLookup;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class FoodScanController extends Controller
{
    public function __invoke(Request $request, ExternalProductLookup $lookup)
    {
        $data = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'add_to_list' => 'nullable|boolean',
            'qty_to_buy' => 'nullable|numeric|min:0.001',
        ]);

        $userId = $request->user()->id;
        $code = $data['code'];
        $barcode = FoodBarcode::with(['product.type', 'product.defaultLocation'])
            ->where('code', $code)
            ->first();

        // Buscar también en el campo barcode del producto principal
        if (!$barcode) {
            $product = FoodProduct::with(['type', 'defaultLocation'])
                ->where('user_id', $userId)
                ->where('barcode', $code)
                ->first();
            if ($product) {
                return $this->buildProductResponse($product, true, $request);
            }
        }

        if ($barcode && $barcode->product->user_id === $userId) {
            return $this->buildProductResponse($barcode->product, true, $request);
        }

        // Buscar externo y autocrear si hay datos
        $external = $lookup->find($code);
        if ($external) {
            $imagePath = null;
            if (!empty($external['image_url'])) {
                $imagePath = $this->downloadImage($external['image_url'], $userId);
            }

            $product = FoodProduct::create([
                'user_id' => $userId,
                'name' => $external['name'] ?? ($data['name'] ?? 'Producto'),
                'brand' => $external['brand'] ?? null,
                'barcode' => $code,
                'unit_base' => $external['unit_base'] ?? 'unit',
                'unit_size' => $external['unit_size'] ?? 1,
                'shelf_life_days' => $external['shelf_life_days'] ?? null,
                'min_stock_qty' => $external['min_stock_qty'] ?? null,
                'image_url' => $external['image_url'] ?? null,
                'presentation_qty' => $external['presentation_qty'] ?? 1,
                'image_path' => $imagePath,
                'description' => $external['description'] ?? null,
            ]);

            FoodBarcode::firstOrCreate([
                'product_id' => $product->id,
                'code' => $code,
            ], ['kind' => 'scan']);

            return $this->buildProductResponse($product->load(['type', 'defaultLocation']), false, $request, true);
        }

        if (!empty($data['name'])) {
            $product = FoodProduct::firstOrCreate(
                ['user_id' => $userId, 'name' => $data['name']],
                ['unit_base' => 'unit', 'unit_size' => 1]
            );

            FoodBarcode::create([
                'product_id' => $product->id,
                'code' => $data['code'],
                'kind' => 'scan',
            ]);

            return $this->buildProductResponse($product, false, $request, true);
        }

        return response()->json([
            'found' => false,
            'message' => 'No encontrado. Proporciona un nombre para crear el producto.',
        ], 404);
    }

    /**
     * Construye respuesta completa con información de stock, precio y alertas
     */
    private function buildProductResponse(
        FoodProduct $product, 
        bool $found, 
        Request $request,
        bool $created = false
    ) {
        // Calcular stock actual
        $currentStock = \App\Models\FoodStockBatch::where('product_id', $product->id)
            ->where('status', 'ok')
            ->sum('qty_remaining_base');

        // Verificar alerta de stock bajo
        $lowStockAlert = false;
        if ($product->min_stock_qty && $currentStock < $product->min_stock_qty) {
            $lowStockAlert = true;
        }

        // Obtener último precio registrado
        $lastPrice = \App\Models\FoodPrice::where('product_id', $product->id)
            ->latest('captured_on')
            ->first();

        // Verificar próximas caducidades
        $expiringBatches = \App\Models\FoodStockBatch::where('product_id', $product->id)
            ->where('status', 'ok')
            ->whereNotNull('expires_on')
            ->where('expires_on', '<=', now()->addDays(7))
            ->count();

        $alerts = [];
        if ($lowStockAlert) {
            $alerts[] = [
                'type' => 'low_stock',
                'message' => "Stock bajo: {$currentStock} {$product->unit_base}. Mínimo recomendado: {$product->min_stock_qty}",
                'severity' => 'warning',
            ];
        }

        if ($expiringBatches > 0) {
            $alerts[] = [
                'type' => 'expiring_soon',
                'message' => "{$expiringBatches} lote(s) próximos a caducar en los próximos 7 días",
                'severity' => 'info',
            ];
        }

        if ($product->performance_index && $product->performance_index < 40) {
            $alerts[] = [
                'type' => 'low_performance',
                'message' => "Producto con bajo rendimiento (índice: {$product->performance_index})",
                'severity' => 'warning',
            ];
        } elseif ($product->performance_index && $product->performance_index >= 80) {
            $alerts[] = [
                'type' => 'high_performance',
                'message' => "Producto con excelente rendimiento (índice: {$product->performance_index})",
                'severity' => 'success',
            ];
        }

        $response = [
            'found' => $found,
            'created' => $created,
            'product' => $product,
            'inventory' => [
                'current_stock' => $currentStock,
                'unit' => $product->unit_base,
                'min_stock' => $product->min_stock_qty,
                'low_stock_alert' => $lowStockAlert,
            ],
            'pricing' => [
                'last_price' => $lastPrice?->price_per_base,
                'vendor' => $lastPrice?->vendor,
                'captured_on' => $lastPrice?->captured_on,
                'currency' => $lastPrice?->currency ?? 'USD',
            ],
            'performance' => [
                'index' => $product->performance_index,
                'avg_consumption_rate' => $product->avg_consumption_rate,
            ],
            'alerts' => $alerts,
        ];

        // Si se solicita agregar a la lista
        if ($request->boolean('add_to_list')) {
            $list = \App\Models\ShoppingList::where('user_id', $request->user()->id)
                ->where('status', 'active')
                ->latest('generated_at')
                ->first();

            if ($list) {
                $qtyToBuy = $request->input('qty_to_buy', $product->min_stock_qty ?? 1);
                
                $item = \App\Models\ShoppingListItem::create([
                    'shopping_list_id' => $list->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'qty_to_buy_base' => $qtyToBuy,
                    'qty_suggested_base' => $qtyToBuy,
                    'qty_current_base' => $currentStock,
                    'unit_base' => $product->unit_base,
                    'unit_size' => $product->unit_size,
                    'estimated_price' => $lastPrice?->price_per_base * $qtyToBuy,
                    'low_stock_alert' => $lowStockAlert,
                    'is_checked' => false,
                    'sort_order' => $list->items()->count(),
                ]);

                $response['added_to_list'] = true;
                $response['list_item'] = $item;
            }
        }

        return response()->json($response);
    }

    private function downloadImage(string $url, int $userId): ?string
    {
        try {
            $response = Http::timeout(6)->get($url);
            if (!$response->ok()) {
                return null;
            }
            $extension = 'jpg';
            $contentType = $response->header('Content-Type');
            if ($contentType && str_contains($contentType, 'png')) {
                $extension = 'png';
            }

            $path = "public/food/products/{$userId}_" . uniqid() . ".{$extension}";
            Storage::put($path, $response->body());

            return Storage::url($path);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
