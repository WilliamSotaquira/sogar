<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ExternalProductLookup
{
    public function find(string $barcode): ?array
    {
        $barcode = trim($barcode);
        if ($barcode === '') {
            return null;
        }

        $product = $this->fromOpenFoodFacts($barcode);

        if (!$product) {
            return null;
        }

        return [
            'name' => $product['name'] ?? null,
            'brand' => $product['brand'] ?? null,
            'unit_base' => $product['unit_base'] ?? 'unit',
            'unit_size' => $product['unit_size'] ?? 1,
            'shelf_life_days' => $product['shelf_life_days'] ?? null,
            'min_stock_qty' => $product['min_stock_qty'] ?? null,
            'image_url' => $product['image_url'] ?? null,
            'description' => $product['description'] ?? null,
        ];
    }

    private function fromOpenFoodFacts(string $barcode): ?array
    {
        return Cache::remember("off:{$barcode}", now()->addHours(12), function () use ($barcode) {
            try {
                $response = Http::timeout(8)
                    ->acceptJson()
                    ->get("https://world.openfoodfacts.org/api/v0/product/{$barcode}.json");

                if (!$response->ok()) {
                    return null;
                }

                $data = $response->json();
                if (($data['status'] ?? 0) !== 1 || empty($data['product'])) {
                    return null;
                }

                $p = $data['product'];
                $name = $p['product_name'] ?? $p['generic_name'] ?? null;
                $brand = $p['brands_tags'][0] ?? ($p['brands'] ?? null);
                [$unitBase, $unitSize] = $this->parseQuantity($p['quantity'] ?? null);

                return [
                    'name' => $name,
                    'brand' => $brand,
                    'unit_base' => $unitBase ?? 'unit',
                    'unit_size' => $unitSize ?? 1,
                    'image_url' => $p['image_front_url'] ?? $p['image_url'] ?? null,
                    'description' => $p['generic_name_es'] ?? $p['generic_name'] ?? $p['ingredients_text'] ?? null,
                ];
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    private function inferUnitBase(?string $size): ?string
    {
        if (!$size) {
            return null;
        }
        $s = Str::lower($size);
        if (str_contains($s, 'kg')) return 'kg';
        if (str_contains($s, 'g')) return 'g';
        if (str_contains($s, 'ml')) return 'ml';
        if (str_contains($s, 'l')) return 'l';
        return 'unit';
    }

    private function inferUnitSize(?string $size): ?float
    {
        if (!$size) {
            return null;
        }
        // Extrae numero
        if (preg_match('/([\d\.,]+)/', $size, $m)) {
            $num = (float) str_replace(',', '.', $m[1]);
            return $num > 0 ? $num : null;
        }
        return null;
    }

    private function parseQuantity(?string $quantity): array
    {
        if (!$quantity) {
            return [null, null];
        }

        $unit = $this->inferUnitBase($quantity);
        $size = $this->inferUnitSize($quantity);

        // Si viene en kg/l convertir a base más granular para consistencia
        if ($unit === 'kg' && $size) {
            return ['g', $size * 1000];
        }
        if ($unit === 'l' && $size) {
            return ['ml', $size * 1000];
        }

        return [$unit, $size];
    }
}
