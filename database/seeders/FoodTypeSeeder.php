<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FoodType;
use App\Models\User;

class FoodTypeSeeder extends Seeder
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

        $types = [
            [
                'name' => 'Lácteos',
                'color' => '#3B82F6', // Azul
                'description' => 'Leche, quesos, yogures, mantequilla',
                'sort_order' => 1,
            ],
            [
                'name' => 'Carnes',
                'color' => '#EF4444', // Rojo
                'description' => 'Res, pollo, cerdo, pescado',
                'sort_order' => 2,
            ],
            [
                'name' => 'Frutas',
                'color' => '#F59E0B', // Naranja
                'description' => 'Frutas frescas y procesadas',
                'sort_order' => 3,
            ],
            [
                'name' => 'Verduras',
                'color' => '#10B981', // Verde
                'description' => 'Vegetales frescos y congelados',
                'sort_order' => 4,
            ],
            [
                'name' => 'Panadería',
                'color' => '#F97316', // Naranja oscuro
                'description' => 'Pan, tortillas, pastelería',
                'sort_order' => 5,
            ],
            [
                'name' => 'Granos y Cereales',
                'color' => '#92400E', // Café
                'description' => 'Arroz, pasta, avena, quinoa',
                'sort_order' => 6,
            ],
            [
                'name' => 'Enlatados',
                'color' => '#6B7280', // Gris
                'description' => 'Conservas, enlatados, encurtidos',
                'sort_order' => 7,
            ],
            [
                'name' => 'Bebidas',
                'color' => '#06B6D4', // Cyan
                'description' => 'Jugos, refrescos, agua, té, café',
                'sort_order' => 8,
            ],
            [
                'name' => 'Snacks',
                'color' => '#8B5CF6', // Púrpura
                'description' => 'Botanas, galletas, dulces',
                'sort_order' => 9,
            ],
            [
                'name' => 'Condimentos',
                'color' => '#DC2626', // Rojo oscuro
                'description' => 'Especias, salsas, aderezos',
                'sort_order' => 10,
            ],
            [
                'name' => 'Congelados',
                'color' => '#60A5FA', // Azul claro
                'description' => 'Productos congelados',
                'sort_order' => 11,
            ],
            [
                'name' => 'Limpieza',
                'color' => '#14B8A6', // Teal
                'description' => 'Productos de limpieza para el hogar',
                'sort_order' => 12,
            ],
            [
                'name' => 'Higiene Personal',
                'color' => '#EC4899', // Rosa
                'description' => 'Jabones, shampoos, cremas',
                'sort_order' => 13,
            ],
        ];

        foreach ($types as $type) {
            FoodType::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $type['name'],
                ],
                [
                    'color' => $type['color'],
                    'description' => $type['description'],
                    'sort_order' => $type['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ ' . count($types) . ' tipos de alimentos creados correctamente.');
    }
}
