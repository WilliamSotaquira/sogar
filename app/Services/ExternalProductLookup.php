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
            'presentation_qty' => $product['presentation_qty'] ?? ($product['unit_size'] ?? 1),
            'raw_quantity' => $product['raw_quantity'] ?? null,
            'categories' => $product['categories'] ?? null,
            'ingredients' => $product['ingredients'] ?? null,
            'portion_text' => $product['portion_text'] ?? null,
            'portion_qty' => $product['portion_qty'] ?? null,
            'portion_unit' => $product['portion_unit'] ?? null,
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
                [$unitBase, $unitSize] = $this->parseQuantityFromProduct($p);
                $categories = $this->extractCategories($p);
                [$portionText, $portionUnit, $portionQty] = $this->parseServingQuantity($p);

                return [
                    'name' => $this->resolveProductName($p),
                    'brand' => $this->resolveBrand($p),
                    'unit_base' => $unitBase ?? 'unit',
                    'unit_size' => $unitSize ?? 1,
                    'image_url' => $p['image_front_url'] ?? $p['image_url'] ?? null,
                    'description' => $p['generic_name_es'] ?? $p['generic_name'] ?? $p['ingredients_text_es'] ?? $p['ingredients_text'] ?? null,
                    'presentation_qty' => $unitSize ?? 1,
                    'raw_quantity' => $this->resolveQuantityText($p),
                    'categories' => $categories,
                    'ingredients' => $p['ingredients_text_es'] ?? $p['ingredients_text'] ?? null,
                    'shelf_life_days' => $this->suggestShelfLife($categories),
                    'portion_text' => $portionText,
                    'portion_qty' => $portionQty,
                    'portion_unit' => $portionUnit,
                ];
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    private function resolveProductName(array $product): ?string
    {
        $candidates = [
            $product['product_name_es'] ?? null,
            $product['product_name'] ?? null,
            $product['product_name_en'] ?? null,
            $product['generic_name_es'] ?? null,
            $product['generic_name'] ?? null,
        ];

        foreach ($candidates as $value) {
            if (is_string($value)) {
                $trimmed = trim($value);
                if ($trimmed !== '') {
                    return $trimmed;
                }
            }
        }

        return null;
    }

    private function resolveBrand(array $product): ?string
    {
        if (!empty($product['brands_tags']) && is_array($product['brands_tags'])) {
            $brand = trim((string) $product['brands_tags'][0]);
            if ($brand !== '') {
                return $brand;
            }
        }

        if (!empty($product['brands'])) {
            $parts = explode(',', $product['brands']);
            $brand = trim($parts[0]);
            if ($brand !== '') {
                return $brand;
            }
        }

        if (!empty($product['brand_owner'])) {
            return trim($product['brand_owner']);
        }

        return null;
    }

    private function parseQuantityFromProduct(array $product): array
    {
        $candidates = [];

        if (!empty($product['quantity'])) {
            $candidates[] = $product['quantity'];
        }

        if (!empty($product['product_quantity'])) {
            $unit = $product['product_quantity_unit'] ?? '';
            $candidates[] = trim($product['product_quantity'] . ' ' . $unit);
        }

        foreach ($candidates as $text) {
            $parsed = $this->parseQuantityText($text);
            if ($parsed) {
                return $parsed;
            }
        }

        return [null, null];
    }

    private function parseServingQuantity(array $product): array
    {
        $candidates = [];
        if (!empty($product['serving_size'])) {
            $candidates[] = $product['serving_size'];
        }

        if (!empty($product['serving_quantity'])) {
            $unit = $product['serving_quantity_unit'] ?? '';
            $candidates[] = trim($product['serving_quantity'] . ' ' . $unit);
        }

        foreach ($candidates as $text) {
            $parsed = $this->parseQuantityText($text);
            if ($parsed) {
                return [$text, $parsed[0], $parsed[1]];
            }
        }

        return [null, null, null];
    }

    private function resolveQuantityText(array $product): ?string
    {
        $candidates = [
            $product['quantity'] ?? null,
            isset($product['product_quantity'], $product['product_quantity_unit'])
                ? "{$product['product_quantity']} {$product['product_quantity_unit']}"
                : null,
            $product['serving_size'] ?? null,
        ];

        foreach ($candidates as $value) {
            if (is_string($value)) {
                $trimmed = trim($value);
                if ($trimmed !== '') {
                    return $trimmed;
                }
            }
        }

        return null;
    }

    private function parseQuantityText(?string $text): ?array
    {
        if (!$text) {
            return null;
        }

        $normalized = Str::lower(Str::ascii($text));
        if (!preg_match_all('/([\d\.,]+)\s*(kg|kilo|kilogramos?|g|gramos?|ml|mililitros?|l|lt|litros?)/i', $normalized, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $match = end($matches);
        $value = (float) str_replace(',', '.', $match[1]);
        if ($value <= 0) {
            return null;
        }

        $unit = $this->mapUnit($match[2]);

        if ($unit === 'kg') {
            return ['g', $value * 1000];
        }

        if ($unit === 'l') {
            return ['ml', $value * 1000];
        }

        return [$unit, $value];
    }

    private function mapUnit(string $unit): string
    {
        $unit = Str::lower(Str::ascii($unit));

        return match (true) {
            str_starts_with($unit, 'kg'), str_starts_with($unit, 'kilo') => 'kg',
            str_starts_with($unit, 'g'), str_starts_with($unit, 'gram') => 'g',
            str_starts_with($unit, 'ml'), str_starts_with($unit, 'mili') => 'ml',
            str_starts_with($unit, 'l'), str_starts_with($unit, 'lt'), str_starts_with($unit, 'lit') => 'l',
            default => 'unit',
        };
    }

    private function extractCategories(array $product): ?string
    {
        if (!empty($product['categories'])) {
            return Str::lower($product['categories']);
        }

        if (!empty($product['categories_tags']) && is_array($product['categories_tags'])) {
            return Str::lower(implode(',', $product['categories_tags']));
        }

        return null;
    }

    private function suggestShelfLife(?string $categories): ?int
    {
        if (!$categories) {
            return 30;
        }

        $categories = Str::lower($categories);

        $rules = [
            ['days' => 7, 'keywords' => ['fresh', 'dairy', 'lacteo', 'leche', 'yogur']],
            ['days' => 3, 'keywords' => ['meat', 'carne', 'fish', 'pescado']],
            ['days' => 5, 'keywords' => ['vegetable', 'verdura', 'fruta', 'fruit']],
            ['days' => 3, 'keywords' => ['bread', 'pan', 'bakery']],
            ['days' => 365, 'keywords' => ['canned', 'conserva', 'enlatado']],
            ['days' => 180, 'keywords' => ['pasta', 'rice', 'arroz', 'cereal', 'grain']],
        ];

        foreach ($rules as $rule) {
            foreach ($rule['keywords'] as $keyword) {
                if (str_contains($categories, $keyword)) {
                    return $rule['days'];
                }
            }
        }

        return 30;
    }
}
