<?php

namespace App\Services;

class UnitConverter
{
    private const MAP = [
        'unit' => 1,
        'g' => 1,
        'kg' => 1000,
        'ml' => 1,
        'l' => 1000,
    ];

    public function toBase(string $unit, float $qty, float $unitSize = 1): float
    {
        $unit = strtolower($unit);
        $factor = self::MAP[$unit] ?? 1;
        return $qty * $unitSize * $factor;
    }

    public function pricePerBase(string $unit, float $qty, float $unitSize, float $price): float
    {
        $baseQty = $this->toBase($unit, $qty, $unitSize);
        return $baseQty > 0 ? $price / $baseQty : 0;
    }
}
