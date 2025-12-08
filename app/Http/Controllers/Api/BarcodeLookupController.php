<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalProductLookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarcodeLookupController extends Controller
{
    /**
     * Buscar información de producto por código de barras
     * Primero en inventario local, luego en OpenFoodFacts
     */
    public function lookup(Request $request, string $code, ExternalProductLookup $lookup): JsonResponse
    {
        $userId = $request->user()->id;

        // 1. Buscar en inventario local
        $localProduct = \App\Models\FoodProduct::where('user_id', $userId)
            ->where(function($q) use ($code) {
                $q->where('barcode', $code)
                  ->orWhereHas('barcodes', fn($bq) => $bq->where('code', $code));
            })
            ->with('type', 'defaultLocation')
            ->first();

        if ($localProduct) {
            return response()->json([
                'found' => true,
                'source' => 'local',
                'data' => [
                    'id' => $localProduct->id,
                    'name' => $localProduct->name,
                    'brand' => $localProduct->brand,
                    'barcode' => $code,
                    'type_id' => $localProduct->type_id,
                    'type_name' => $localProduct->type?->name,
                    'location_id' => $localProduct->default_location_id,
                    'location_name' => $localProduct->defaultLocation?->name,
                    'unit_base' => $localProduct->unit_base,
                    'unit_size' => $localProduct->unit_size,
                    'min_stock_qty' => $localProduct->min_stock_qty,
                    'shelf_life_days' => $localProduct->shelf_life_days,
                    'image_url' => $localProduct->image_url ?? $localProduct->image_path,
                    'presentation_qty' => $localProduct->presentation_qty,
                    'portion_qty' => $localProduct->presentation_qty,
                ],
            ]);
        }

        // 2. Buscar en OpenFoodFacts
        $external = $lookup->find($code);
        if ($external) {
            return response()->json([
                'found' => true,
                'source' => 'openfoodfacts',
                'data' => [
                    'name' => $external['name'] ?? '',
                    'brand' => $external['brand'] ?? '',
                    'barcode' => $code,
                    'image_url' => $external['image_url'] ?? null,
                    'categories' => $external['categories'] ?? '',
                    'quantity' => $external['raw_quantity'] ?? '',
                    'ingredients' => $external['ingredients'] ?? '',
                    'unit_base' => $external['unit_base'] ?? 'unit',
                    'unit_size' => $external['unit_size'] ?? 1,
                    'suggested_shelf_life' => $external['shelf_life_days'] ?? null,
                    'portion_text' => $external['portion_text'] ?? '',
                    'portion_qty' => $external['portion_qty'] ?? null,
                    'portion_unit' => $external['portion_unit'] ?? null,
                ],
            ]);
        }

        // 3. No encontrado
        return response()->json([
            'found' => false,
            'source' => null,
            'message' => 'Producto no encontrado en inventario local ni en OpenFoodFacts',
        ], 404);
    }

}
