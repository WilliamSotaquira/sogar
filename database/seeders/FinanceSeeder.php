<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\CategoryKeyword;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $year = (int) $now->format('Y');
        $month = (int) $now->format('m');

        $categories = [
            ['name' => 'Salario', 'type' => 'income', 'color' => '#22c55e'],
            ['name' => 'Bonos', 'type' => 'income', 'color' => '#0ea5e9'],
            ['name' => 'Mercado', 'type' => 'expense', 'color' => '#f97316'],
            ['name' => 'Vivienda', 'type' => 'expense', 'color' => '#6366f1'],
            ['name' => 'Transporte', 'type' => 'expense', 'color' => '#8b5cf6'],
            ['name' => 'Educación', 'type' => 'expense', 'color' => '#06b6d4'],
            ['name' => 'Salud', 'type' => 'expense', 'color' => '#ef4444'],
            ['name' => 'Ocio', 'type' => 'expense', 'color' => '#f59e0b'],
            ['name' => 'Ahorro', 'type' => 'expense', 'color' => '#10b981'],
        ];

        $categoryRecords = collect($categories)->mapWithKeys(function ($data) {
            $category = Category::firstOrCreate(
                ['name' => $data['name'], 'type' => $data['type'], 'user_id' => null],
                ['color' => $data['color']]
            );

            return [$category->name => $category];
        });

        $users = User::all();
        foreach ($users as $user) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id, 'name' => 'General'],
                [
                    'description' => 'Bolsillo principal',
                    'initial_balance' => 0,
                    'is_shared' => true,
                ]
            );

            $mercado = $categoryRecords->get('Mercado');
            if ($mercado) {
                CategoryKeyword::firstOrCreate(
                    ['user_id' => $user->id, 'keyword' => 'super'],
                    ['category_id' => $mercado->id]
                );

                Budget::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'category_id' => $mercado->id,
                        'month' => $month,
                        'year' => $year,
                    ],
                    [
                        'amount' => 500000,
                        'is_flexible' => false,
                        'sync_to_calendar' => false,
                    ]
                );
            }

            $ahorro = $categoryRecords->get('Ahorro');
            if ($ahorro) {
                Budget::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'category_id' => $ahorro->id,
                        'month' => $month,
                        'year' => $year,
                    ],
                    [
                        'amount' => 200000,
                        'is_flexible' => true,
                        'sync_to_calendar' => false,
                    ]
                );
            }
        }

        $this->command?->info('✅ Finanzas básicas creadas (categorías, bolsillo general, presupuestos de ejemplo).');
    }
}
