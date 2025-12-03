<?php

namespace Database\Seeders;

use App\Models\FoodLocation;
use App\Models\FoodType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FoodDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        // Para entornos multiusuario, se puede ajustar para iterar por usuarios.
        $userId = Auth::id() ?? 1;

        $locations = [
            ['name' => 'Despensa', 'color' => '#0ea5e9'],
            ['name' => 'Refrigerador', 'color' => '#22c55e'],
            ['name' => 'Congelador', 'color' => '#3b82f6'],
            ['name' => 'Alacena', 'color' => '#f97316'],
        ];

        foreach ($locations as $index => $loc) {
            FoodLocation::firstOrCreate(
                ['user_id' => $userId, 'slug' => Str::slug($loc['name'])],
                [
                    'name' => $loc['name'],
                    'color' => $loc['color'],
                    'sort_order' => $index,
                    'is_default' => $index === 0,
                ]
            );
        }

        $types = [
            'Lácteos',
            'Granos',
            'Proteínas',
            'Verduras',
            'Frutas',
            'Condimentos',
            'Snacks',
            'Bebidas',
            'Limpieza',
        ];

        foreach ($types as $index => $type) {
            FoodType::firstOrCreate(
                ['user_id' => $userId, 'name' => $type],
                [
                    'description' => null,
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }
    }
}
