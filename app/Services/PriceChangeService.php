<?php

namespace App\Services;

use App\Models\FoodProduct;
use App\Models\FoodPrice;
use Carbon\Carbon;

class PriceChangeService
{
    /**
     * Registra un nuevo precio y calcula el cambio respecto al anterior
     * 
     * @param FoodProduct $product
     * @param float $newPrice Precio por unidad base
     * @param string|null $vendor Nombre del proveedor
     * @param string $source manual|scan|ticket
     * @param string|null $note Notas adicionales
     * @return array Información sobre el cambio de precio
     */
    public function registerPriceChange(
        FoodProduct $product,
        float $newPrice,
        ?string $vendor = null,
        string $source = 'manual',
        ?string $note = null
    ): array {
        // Obtener el precio anterior más reciente
        $previousPrice = FoodPrice::where('product_id', $product->id)
            ->latest('captured_on')
            ->first();

        $priceChange = null;
        $priceChangePercent = null;
        $isPriceAlert = false;

        if ($previousPrice) {
            $priceChange = $newPrice - $previousPrice->price_per_base;
            
            if ($previousPrice->price_per_base > 0) {
                $priceChangePercent = ($priceChange / $previousPrice->price_per_base) * 100;
                
                // Generar alerta si el cambio es significativo (>10% arriba o >15% abajo)
                if (abs($priceChangePercent) > 10) {
                    $isPriceAlert = true;
                }
            }
        }

        // Registrar el nuevo precio
        $foodPrice = FoodPrice::create([
            'product_id' => $product->id,
            'source' => $source,
            'vendor' => $vendor,
            'currency' => 'USD',
            'price_per_base' => $newPrice,
            'captured_on' => Carbon::today(),
            'note' => $note,
            'price_change_percent' => $priceChangePercent,
            'is_price_alert' => $isPriceAlert,
        ]);

        return [
            'price_recorded' => $foodPrice,
            'previous_price' => $previousPrice?->price_per_base,
            'price_change' => $priceChange,
            'price_change_percent' => $priceChangePercent,
            'is_alert' => $isPriceAlert,
            'alert_message' => $this->generateAlertMessage(
                $product->name,
                $priceChangePercent,
                $previousPrice?->price_per_base,
                $newPrice,
                $vendor
            ),
        ];
    }

    /**
     * Obtiene el historial de precios de un producto
     */
    public function getPriceHistory(FoodProduct $product, int $months = 6): array
    {
        $prices = FoodPrice::where('product_id', $product->id)
            ->where('captured_on', '>=', Carbon::now()->subMonths($months))
            ->orderBy('captured_on')
            ->get();

        $history = $prices->map(function ($price) {
            return [
                'date' => $price->captured_on->format('Y-m-d'),
                'price' => $price->price_per_base,
                'vendor' => $price->vendor,
                'source' => $price->source,
                'change_percent' => $price->price_change_percent,
            ];
        });

        return [
            'product' => $product->name,
            'history' => $history,
            'avg_price' => $prices->avg('price_per_base'),
            'min_price' => $prices->min('price_per_base'),
            'max_price' => $prices->max('price_per_base'),
            'best_vendor' => $this->findBestVendor($prices),
        ];
    }

    /**
     * Encuentra el proveedor con mejor precio promedio
     */
    private function findBestVendor($prices)
    {
        $byVendor = $prices->groupBy('vendor')->map(function ($group) {
            return [
                'avg_price' => $group->avg('price_per_base'),
                'count' => $group->count(),
            ];
        })->sortBy('avg_price');

        $best = $byVendor->first();
        
        if (!$best) {
            return null;
        }

        return [
            'name' => $byVendor->keys()->first(),
            'avg_price' => $best['avg_price'],
            'times_purchased' => $best['count'],
        ];
    }

    /**
     * Genera mensaje de alerta sobre cambio de precio
     */
    private function generateAlertMessage(
        string $productName,
        ?float $changePercent,
        ?float $oldPrice,
        float $newPrice,
        ?string $vendor
    ): ?string {
        if ($changePercent === null) {
            return "Primer precio registrado para {$productName}: \${$newPrice}";
        }

        $vendorText = $vendor ? " en {$vendor}" : "";
        
        if ($changePercent > 10) {
            return "⚠️ El precio de {$productName} ha subido {$changePercent}%{$vendorText} " .
                   "(de \${$oldPrice} a \${$newPrice})";
        } elseif ($changePercent < -15) {
            return "✅ El precio de {$productName} ha bajado " . abs($changePercent) . "%{$vendorText} " .
                   "(de \${$oldPrice} a \${$newPrice})";
        }

        return null;
    }

    /**
     * Compara precios entre diferentes proveedores para el mismo producto
     */
    public function comparePricesByVendor(FoodProduct $product): array
    {
        $prices = FoodPrice::where('product_id', $product->id)
            ->whereNotNull('vendor')
            ->where('captured_on', '>=', Carbon::now()->subMonths(3))
            ->get()
            ->groupBy('vendor');

        $comparison = [];

        foreach ($prices as $vendor => $vendorPrices) {
            $avgPrice = $vendorPrices->avg('price_per_base');
            $lastPrice = $vendorPrices->sortByDesc('captured_on')->first();
            
            $comparison[] = [
                'vendor' => $vendor,
                'avg_price' => round($avgPrice, 2),
                'last_price' => $lastPrice->price_per_base,
                'last_date' => $lastPrice->captured_on->format('Y-m-d'),
                'price_count' => $vendorPrices->count(),
            ];
        }

        // Ordenar por precio promedio
        usort($comparison, fn($a, $b) => $a['avg_price'] <=> $b['avg_price']);

        return $comparison;
    }

    /**
     * Obtener alertas de precio para el dashboard
     */
    public function getPriceAlerts(int $userId, int $days = 7): array
    {
        $alerts = FoodPrice::with('product')
            ->whereHas('product', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('is_price_alert', true)
            ->where('captured_on', '>=', Carbon::now()->subDays($days))
            ->orderByDesc('captured_on')
            ->get();

        return $alerts->map(function ($price) {
            return [
                'product' => $price->product->name,
                'vendor' => $price->vendor,
                'price' => $price->price_per_base,
                'change_percent' => $price->price_change_percent,
                'date' => $price->captured_on->format('d/m/Y'),
                'severity' => $price->price_change_percent > 0 ? 'warning' : 'success',
            ];
        })->toArray();
    }
}
