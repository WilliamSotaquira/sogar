<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FoodProduct;
use App\Services\ProductPerformanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductPerformanceController extends Controller
{
    public function calculate(Request $request, FoodProduct $product, ProductPerformanceService $service): JsonResponse
    {
        // Verificar que el producto pertenece al usuario
        if ($product->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $index = $service->calculatePerformanceIndex($product);

        return response()->json([
            'success' => true,
            'performance_index' => $index,
            'avg_consumption_rate' => $product->avg_consumption_rate,
            'last_calc' => $product->last_performance_calc?->format('Y-m-d'),
            'message' => $this->getPerformanceMessage($index),
        ]);
    }

    private function getPerformanceMessage(float $index): string
    {
        if ($index >= 80) {
            return '🌟 Excelente rendimiento. Es un producto altamente recomendable.';
        } elseif ($index >= 60) {
            return '✅ Buen rendimiento. Producto confiable.';
        } elseif ($index >= 40) {
            return '⚠️ Rendimiento regular. Considera monitorear.';
        } else {
            return '❌ Bajo rendimiento. Evalúa alternativas o ajusta compras.';
        }
    }
}
