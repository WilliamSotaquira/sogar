<?php

namespace App\Services;

use App\Models\FoodProduct;
use App\Models\ConsumptionLog;
use App\Models\FoodPrice;
use App\Models\FoodStockBatch;
use Carbon\Carbon;

class ProductPerformanceService
{
    /**
     * Calcula el índice de rendimiento/rentabilidad de un producto
     * 
     * Factores considerados:
     * - Duración del producto (shelf_life_days)
     * - Tasa de consumo histórica
     * - Estabilidad de precio
     * - Desperdicio (batches con status wasted/expired)
     * - Costo-beneficio
     * 
     * Resultado: 0-100 donde:
     * - 80-100: Excelente rendimiento
     * - 60-79: Buen rendimiento
     * - 40-59: Rendimiento regular
     * - 0-39: Bajo rendimiento
     */
    public function calculatePerformanceIndex(FoodProduct $product): float
    {
        $score = 50; // Base neutra

        // 1. Factor duración (20 puntos)
        if ($product->shelf_life_days) {
            if ($product->shelf_life_days >= 180) {
                $score += 20; // Productos de larga duración
            } elseif ($product->shelf_life_days >= 30) {
                $score += 15;
            } elseif ($product->shelf_life_days >= 7) {
                $score += 10;
            } else {
                $score += 5; // Duración muy corta
            }
        } else {
            $score += 15; // Sin caducidad (productos secos, enlatados, etc.)
        }

        // 2. Factor desperdicio (-30 puntos potenciales)
        $wasteRate = $this->calculateWasteRate($product);
        if ($wasteRate > 0.3) {
            $score -= 30; // Más del 30% se desperdicia
        } elseif ($wasteRate > 0.15) {
            $score -= 20;
        } elseif ($wasteRate > 0.05) {
            $score -= 10;
        } else {
            $score += 10; // Muy poco desperdicio
        }

        // 3. Factor rotación/consumo (20 puntos)
        $avgConsumption = $this->calculateAverageConsumptionRate($product);
        if ($avgConsumption > 0) {
            $product->avg_consumption_rate = $avgConsumption;
            
            if ($product->shelf_life_days) {
                $daysToConsume = $product->min_stock_qty / $avgConsumption;
                
                if ($daysToConsume < $product->shelf_life_days * 0.5) {
                    $score += 20; // Se consume rápido antes de caducar
                } elseif ($daysToConsume < $product->shelf_life_days * 0.8) {
                    $score += 10;
                } else {
                    $score -= 10; // Consumo lento, riesgo de caducidad
                }
            } else {
                $score += 10; // Consumo regular, sin caducidad
            }
        }

        // 4. Factor estabilidad de precio (15 puntos)
        $priceVolatility = $this->calculatePriceVolatility($product);
        if ($priceVolatility < 0.1) {
            $score += 15; // Precio muy estable
        } elseif ($priceVolatility < 0.25) {
            $score += 8;
        } elseif ($priceVolatility > 0.5) {
            $score -= 10; // Precio muy volátil
        }

        // 5. Factor disponibilidad (5 puntos)
        $stockOut = $this->calculateStockOutFrequency($product);
        if ($stockOut < 0.1) {
            $score += 5; // Siempre disponible
        } elseif ($stockOut > 0.3) {
            $score -= 5; // Frecuentemente sin stock
        }

        // Normalizar entre 0-100
        $finalScore = max(0, min(100, $score));
        
        $product->performance_index = $finalScore;
        $product->last_performance_calc = Carbon::today();
        $product->save();

        return $finalScore;
    }

    /**
     * Calcula la tasa de desperdicio
     */
    private function calculateWasteRate(FoodProduct $product): float
    {
        $totalBatches = FoodStockBatch::where('product_id', $product->id)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->count();

        if ($totalBatches === 0) {
            return 0;
        }

        $wastedBatches = FoodStockBatch::where('product_id', $product->id)
            ->whereIn('status', ['wasted', 'expired'])
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->count();

        return $wastedBatches / $totalBatches;
    }

    /**
     * Calcula tasa promedio de consumo (unidades base por día)
     */
    private function calculateAverageConsumptionRate(FoodProduct $product): float
    {
        $logs = ConsumptionLog::where('product_id', $product->id)
            ->where('occurred_on', '>=', Carbon::now()->subMonths(3))
            ->get();

        if ($logs->isEmpty()) {
            // Intentar estimar desde movimientos de stock
            $movements = $product->movements()
                ->where('reason', 'consume')
                ->where('occurred_on', '>=', Carbon::now()->subMonths(3))
                ->get();

            if ($movements->isEmpty()) {
                return 0;
            }

            $totalConsumed = $movements->sum('qty_delta_base');
            $days = 90; // 3 meses

            return abs($totalConsumed) / $days;
        }

        $totalConsumed = $logs->sum('qty_base');
        $firstLog = $logs->min('occurred_on');
        $lastLog = $logs->max('occurred_on');
        
        $days = Carbon::parse($firstLog)->diffInDays(Carbon::parse($lastLog)) ?: 1;

        return $totalConsumed / $days;
    }

    /**
     * Calcula volatilidad del precio (desviación estándar relativa)
     */
    private function calculatePriceVolatility(FoodProduct $product): float
    {
        $prices = FoodPrice::where('product_id', $product->id)
            ->where('captured_on', '>=', Carbon::now()->subMonths(6))
            ->pluck('price_per_base')
            ->toArray();

        if (count($prices) < 2) {
            return 0; // Sin suficientes datos
        }

        $mean = array_sum($prices) / count($prices);
        
        if ($mean == 0) {
            return 0;
        }

        $variance = array_sum(array_map(function($price) use ($mean) {
            return pow($price - $mean, 2);
        }, $prices)) / count($prices);

        $stdDev = sqrt($variance);

        return $stdDev / $mean; // Coeficiente de variación
    }

    /**
     * Frecuencia de desabastecimiento
     */
    private function calculateStockOutFrequency(FoodProduct $product): float
    {
        // Revisar últimos 90 días
        $batches = FoodStockBatch::where('product_id', $product->id)
            ->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->orderBy('created_at')
            ->get(['created_at', 'qty_remaining_base']);

        if ($batches->isEmpty()) {
            return 0;
        }

        $daysWithoutStock = 0;
        $totalDays = 90;

        // Simplificación: contar días desde última compra
        $lastPurchase = $batches->max('created_at');
        $daysSinceLastPurchase = Carbon::parse($lastPurchase)->diffInDays(Carbon::now());

        $currentStock = FoodStockBatch::where('product_id', $product->id)
            ->where('status', 'ok')
            ->sum('qty_remaining_base');

        if ($currentStock == 0 && $daysSinceLastPurchase > 7) {
            $daysWithoutStock = min($daysSinceLastPurchase, 30);
        }

        return min(1, $daysWithoutStock / $totalDays);
    }

    /**
     * Genera alertas de rendimiento para todos los productos del usuario
     */
    public function generatePerformanceAlerts(int $userId): array
    {
        $products = FoodProduct::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        $alerts = [
            'low_performance' => [],
            'high_performance' => [],
        ];

        foreach ($products as $product) {
            $score = $this->calculatePerformanceIndex($product);

            if ($score <= 40) {
                $alerts['low_performance'][] = [
                    'product' => $product,
                    'score' => $score,
                    'message' => "El producto '{$product->name}' tiene bajo rendimiento. Considera reducir compras o buscar alternativas.",
                ];
            } elseif ($score >= 80) {
                $alerts['high_performance'][] = [
                    'product' => $product,
                    'score' => $score,
                    'message' => "El producto '{$product->name}' tiene excelente rendimiento. Es una buena opción de compra.",
                ];
            }
        }

        return $alerts;
    }
}
