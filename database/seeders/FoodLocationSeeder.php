<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FoodLocation;
use App\Models\User;
use Illuminate\Support\Str;

class FoodLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el usuario actual o el primero disponible
        $user = User::first();

        if (!$user) {
            $this->command->warn('No hay usuarios en la base de datos. Crea un usuario primero.');
            return;
        }

        $locations = [
            [
                'name' => 'Refrigerador',
                'color' => '#3B82F6', // Azul
                'sort_order' => 1,
                'is_default' => true,
            ],
            [
                'name' => 'Congelador',
                'color' => '#60A5FA', // Azul claro
                'sort_order' => 2,
                'is_default' => false,
            ],
            [
                'name' => 'Despensa',
                'color' => '#92400E', // Café
                'sort_order' => 3,
                'is_default' => false,
            ],
            [
                'name' => 'Cocina - Alacena Alta',
                'color' => '#F59E0B', // Naranja
                'sort_order' => 4,
                'is_default' => false,
            ],           [
                'name' => 'Baño',
                'color' => '#14B8A6', // Teal
                'sort_order' => 5,
                'is_default' => false,
            ],
            [
                'name' => 'Lavandería',
                'color' => '#06B6D4', // Cyan
                'sort_order' => 6,
                'is_default' => false,
            ],
            [
                'name' => 'Habitación - Alacena Alta',
                'color' => '#F97316', // Naranja oscuro
                'sort_order' => 7,
                'is_default' => false,
            ],
            [
                'name' => 'Otra Ubicación',
                'color' => '#DC2626', // Rojo
                'sort_order' => 8,
                'is_default' => false,
            ],
        ];

        foreach ($locations as $location) {
            FoodLocation::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $location['name'],
                ],
                [
                    'slug' => Str::slug($location['name']),
                    'color' => $location['color'],
                    'sort_order' => $location['sort_order'],
                    'is_default' => $location['is_default'],
                ]
            );
        }

        $this->command->info('✅ ' . count($locations) . ' ubicaciones de almacenamiento creadas correctamente.');
    }
}
