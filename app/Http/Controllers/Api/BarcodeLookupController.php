<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BarcodeLookupController extends Controller
{
    /**
     * Buscar información de producto por código de barras
     * Primero en inventario local, luego en OpenFoodFacts
     */
    public function lookup(Request $request, string $code): JsonResponse
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
                ],
            ]);
        }

        // 2. Buscar en OpenFoodFacts
        try {
            $response = Http::timeout(5)->get("https://world.openfoodfacts.org/api/v0/product/{$code}.json");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === 1 && isset($data['product'])) {
                    $product = $data['product'];
                    
                    return response()->json([
                        'found' => true,
                        'source' => 'openfoodfacts',
                        'data' => [
                            'name' => $product['product_name'] ?? $product['generic_name'] ?? '',
                            'brand' => $product['brands'] ?? '',
                            'barcode' => $code,
                            'image_url' => $product['image_url'] ?? $product['image_front_url'] ?? null,
                            'categories' => $product['categories'] ?? '',
                            'quantity' => $product['quantity'] ?? '',
                            'ingredients' => $product['ingredients_text'] ?? '',
                            // Inferir unidad base de la cantidad
                            'unit_base' => $this->inferUnitBase($product['quantity'] ?? ''),
                            'unit_size' => $this->extractQuantity($product['quantity'] ?? ''),
                            'suggested_shelf_life' => $this->suggestShelfLife($product['categories'] ?? ''),
                        ],
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('OpenFoodFacts error: ' . $e->getMessage());
        }

        // 3. No encontrado
        return response()->json([
            'found' => false,
            'source' => null,
            'message' => 'Producto no encontrado en inventario local ni en OpenFoodFacts',
        ], 404);
    }

    /**
     * Inferir unidad base desde texto de cantidad
     */
    private function inferUnitBase(string $quantity): string
    {
        $quantity = strtolower($quantity);
        
        if (str_contains($quantity, 'ml') || str_contains($quantity, 'mililitr')) {
            return 'ml';
        }
        if (str_contains($quantity, 'l') || str_contains($quantity, 'litr')) {
            return 'l';
        }
        if (str_contains($quantity, 'kg') || str_contains($quantity, 'kilogr')) {
            return 'kg';
        }
        if (str_contains($quantity, 'g') || str_contains($quantity, 'gram')) {
            return 'g';
        }
        
        return 'unit';
    }

    /**
     * Extraer cantidad numérica del texto
     */
    private function extractQuantity(string $quantity): float
    {
        if (preg_match('/(\d+\.?\d*)/', $quantity, $matches)) {
            return (float) $matches[1];
        }
        
        return 1.0;
    }

    /**
     * Sugerir vida útil basada en categorías
     */
    private function suggestShelfLife(string $categories): ?int
    {
        $categories = strtolower($categories);
        
        // Categorías que duran poco
        if (str_contains($categories, 'fresh') || 
            str_contains($categories, 'dairy') || 
            str_contains($categories, 'lácteo') ||
            str_contains($categories, 'leche') ||
            str_contains($categories, 'yogur')) {
            return 7; // 1 semana
        }
        
        if (str_contains($categories, 'meat') || 
            str_contains($categories, 'carne') ||
            str_contains($categories, 'fish') ||
            str_contains($categories, 'pescado')) {
            return 3; // 3 días
        }
        
        if (str_contains($categories, 'vegetables') || 
            str_contains($categories, 'verdura') ||
            str_contains($categories, 'fruta') ||
            str_contains($categories, 'fruit')) {
            return 5; // 5 días
        }
        
        if (str_contains($categories, 'bread') || 
            str_contains($categories, 'pan')) {
            return 3; // 3 días
        }
        
        // Categorías que duran mucho
        if (str_contains($categories, 'canned') || 
            str_contains($categories, 'conserva') ||
            str_contains($categories, 'enlatado')) {
            return 365; // 1 año
        }
        
        if (str_contains($categories, 'pasta') || 
            str_contains($categories, 'rice') ||
            str_contains($categories, 'arroz') ||
            str_contains($categories, 'cereal')) {
            return 180; // 6 meses
        }
        
        // Por defecto
        return 30; // 1 mes
    }
}
