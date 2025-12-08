<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\FoodPrice;
use App\Models\FoodProduct;
use App\Services\PriceChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PriceController extends Controller
{
    /**
     * Mostrar gestión de precios de un producto
     */
    public function show(Request $request, FoodProduct $product): View
    {
        $this->authorizeProductAccess($request, $product);

        // Obtener histórico de precios
        $priceHistory = FoodPrice::where('product_id', $product->id)
            ->orderBy('captured_on', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Estadísticas
        $stats = $this->calculateStats($product);

        // Precio actual y anterior
        $currentPrice = $priceHistory->first();
        $previousPrice = $priceHistory->skip(1)->first();

        // Tendencia (últimos 30 días)
        $trend = $this->calculateTrend($product);

        return view('food.prices.show', compact(
            'product',
            'priceHistory',
            'stats',
            'currentPrice',
            'previousPrice',
            'trend'
        ));
    }

    /**
     * Registrar nuevo precio
     */
    public function store(Request $request, FoodProduct $product, PriceChangeService $service): JsonResponse
    {
        $this->authorizeProductAccess($request, $product);

        $data = $request->validate([
            'price' => 'required|numeric|min:0.01',
            'vendor' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:50',
            'note' => 'nullable|string|max:500',
            'captured_on' => 'nullable|date',
        ]);

        $price = $service->registerPriceChange(
            $product->id,
            $data['price'],
            $data['vendor'] ?? null,
            $data['source'] ?? 'manual',
            $data['note'] ?? null,
            $data['captured_on'] ?? now()
        );

        return response()->json([
            'success' => true,
            'price' => $price,
            'message' => 'Precio registrado correctamente',
        ]);
    }

    /**
     * Obtener datos para gráfica
     */
    public function chartData(Request $request, FoodProduct $product): JsonResponse
    {
        $this->authorizeProductAccess($request, $product);

        $days = $request->integer('days', 90);

        $prices = FoodPrice::where('product_id', $product->id)
            ->where('captured_on', '>=', now()->subDays($days))
            ->orderBy('captured_on', 'asc')
            ->get(['captured_on', 'price_per_base', 'vendor'])
            ->map(function ($price) {
                return [
                    'date' => $price->captured_on->format('Y-m-d'),
                    'price' => (float) $price->price_per_base,
                    'vendor' => $price->vendor,
                ];
            });

        return response()->json([
            'labels' => $prices->pluck('date'),
            'data' => $prices->pluck('price'),
            'vendors' => $prices->pluck('vendor'),
        ]);
    }

    /**
     * Proyección de precios futuros (simple linear regression)
     */
    public function forecast(Request $request, FoodProduct $product): JsonResponse
    {
        $this->authorizeProductAccess($request, $product);

        $days = $request->integer('days', 30); // Días hacia adelante

        // Obtener últimos 60 días de datos
        $historicalPrices = FoodPrice::where('product_id', $product->id)
            ->where('captured_on', '>=', now()->subDays(60))
            ->orderBy('captured_on', 'asc')
            ->get(['captured_on', 'price_per_base']);

        if ($historicalPrices->count() < 3) {
            return response()->json([
                'error' => 'No hay suficientes datos históricos para proyectar',
            ], 400);
        }

        // Regresión lineal simple
        $forecast = $this->linearRegression($historicalPrices, $days);

        return response()->json([
            'historical' => $historicalPrices->map(fn($p) => [
                'date' => $p->captured_on->format('Y-m-d'),
                'price' => (float) $p->price_per_base,
            ]),
            'forecast' => $forecast,
            'trend' => $forecast['slope'] > 0 ? 'increasing' : ($forecast['slope'] < 0 ? 'decreasing' : 'stable'),
        ]);
    }

    /**
     * Calcular estadísticas
     */
    private function calculateStats(FoodProduct $product): array
    {
        $prices = FoodPrice::where('product_id', $product->id)
            ->where('captured_on', '>=', now()->subDays(365))
            ->pluck('price_per_base');

        if ($prices->isEmpty()) {
            return [
                'avg' => 0,
                'min' => 0,
                'max' => 0,
                'median' => 0,
                'volatility' => 0,
                'total_records' => 0,
            ];
        }

        $sorted = $prices->sort()->values();
        $count = $sorted->count();
        $median = $count % 2 === 0
            ? ($sorted[$count / 2 - 1] + $sorted[$count / 2]) / 2
            : $sorted[floor($count / 2)];

        // Volatilidad (desviación estándar)
        $avg = $prices->avg();
        $variance = $prices->map(fn($p) => pow($p - $avg, 2))->avg();
        $volatility = sqrt($variance);

        return [
            'avg' => round($avg, 2),
            'min' => round($prices->min(), 2),
            'max' => round($prices->max(), 2),
            'median' => round($median, 2),
            'volatility' => round($volatility, 2),
            'total_records' => $count,
        ];
    }

    /**
     * Calcular tendencia (últimos 30 días)
     */
    private function calculateTrend(FoodProduct $product): array
    {
        $recent = FoodPrice::where('product_id', $product->id)
            ->where('captured_on', '>=', now()->subDays(30))
            ->orderBy('captured_on', 'asc')
            ->get(['price_per_base']);

        if ($recent->count() < 2) {
            return ['direction' => 'stable', 'change' => 0];
        }

        $first = $recent->first()->price_per_base;
        $last = $recent->last()->price_per_base;
        $change = (($last - $first) / $first) * 100;

        return [
            'direction' => abs($change) < 2 ? 'stable' : ($change > 0 ? 'up' : 'down'),
            'change' => round($change, 2),
        ];
    }

    /**
     * Regresión lineal simple para proyección
     */
    private function linearRegression($prices, $futureUnits): array
    {
        $n = $prices->count();
        $x = range(0, $n - 1);
        $y = $prices->pluck('price_per_base')->map(fn($p) => (float) $p)->toArray();

        // Calcular pendiente y intercepción
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        // Proyectar hacia adelante
        $forecasted = [];
        $lastDate = $prices->last()->captured_on;

        for ($i = 1; $i <= $futureUnits; $i++) {
            $date = $lastDate->copy()->addDays($i);
            $predictedPrice = $slope * ($n + $i - 1) + $intercept;

            $forecasted[] = [
                'date' => $date->format('Y-m-d'),
                'price' => round(max(0, $predictedPrice), 2), // No permitir precios negativos
            ];
        }

        return [
            'data' => $forecasted,
            'slope' => round($slope, 4),
            'intercept' => round($intercept, 2),
        ];
    }

    /**
     * Autorizar acceso
     */
    private function authorizeProductAccess(Request $request, FoodProduct $product): void
    {
        abort_unless($product->user_id === $request->user()->id, 403);
    }
}
