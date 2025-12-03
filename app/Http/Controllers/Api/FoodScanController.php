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
                return response()->json([
                    'found' => true,
                    'product' => $product,
                ]);
            }
        }

        if ($barcode && $barcode->product->user_id === $userId) {
            return response()->json([
                'found' => true,
                'product' => $barcode->product,
            ]);
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
                'image_path' => $imagePath,
                'description' => $external['description'] ?? null,
            ]);

            FoodBarcode::firstOrCreate([
                'product_id' => $product->id,
                'code' => $code,
            ], ['kind' => 'scan']);

            return response()->json([
                'found' => false,
                'created' => true,
                'product' => $product->load(['type', 'defaultLocation']),
            ]);
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

            return response()->json([
                'found' => false,
                'created' => true,
                'product' => $product,
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'No encontrado',
        ], 404);
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
