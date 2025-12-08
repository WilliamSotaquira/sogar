<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\FoodLocation;
use App\Models\FoodProduct;
use App\Models\FoodStockBatch;
use App\Models\FoodType;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FoodShoppingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'william@sogar.com')->first();

        if (!$user) {
            $this->command->error('No existe el usuario william@sogar.com. Ejecuta primero UserSeeder.');
            return;
        }

        // Familia demo
        $family = FamilyGroup::firstOrCreate(
            ['name' => 'Familia Demo', 'admin_user_id' => $user->id],
            ['description' => 'Núcleo de prueba', 'is_active' => true]
        );

        FamilyMember::firstOrCreate(
            ['family_group_id' => $family->id, 'user_id' => $user->id],
            [
                'role' => 'padre',
                'is_admin' => true,
                'can_manage_finances' => true,
                'can_manage_food' => true,
                'can_manage_shopping' => true,
                'joined_at' => now(),
            ]
        );

        $user->update(['active_family_group_id' => $family->id]);

        // Categoría y presupuesto
        $category = Category::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Mercado Demo'],
            ['type' => 'expense', 'color' => '#10b981', 'is_active' => true]
        );

        $budget = Budget::firstOrCreate(
            [
                'user_id' => $user->id,
                'category_id' => $category->id,
                'month' => (int) now()->format('m'),
                'year' => (int) now()->format('Y'),
            ],
            [
                'amount' => 250.00,
                'is_flexible' => false,
            ]
        );

        // Tipos y ubicaciones
        $types = collect([
            ['name' => 'Lácteos', 'color' => '#60a5fa'],
            ['name' => 'Verduras', 'color' => '#34d399'],
            ['name' => 'Aseo', 'color' => '#f59e0b'],
        ])->mapWithKeys(function ($data, $index) use ($user) {
            $type = FoodType::firstOrCreate(
                ['user_id' => $user->id, 'name' => $data['name']],
                ['description' => 'Tipo demo', 'color' => $data['color'], 'sort_order' => $index]
            );
            return [$data['name'] => $type->id];
        });

        $locations = collect([
            ['name' => 'Despensa', 'color' => '#10b981'],
            ['name' => 'Refrigerador', 'color' => '#0ea5e9'],
            ['name' => 'Limpieza', 'color' => '#f97316'],
        ])->mapWithKeys(function ($data, $index) use ($user) {
            $loc = FoodLocation::firstOrCreate(
                ['user_id' => $user->id, 'slug' => Str::slug($data['name'])],
                ['name' => $data['name'], 'color' => $data['color'], 'sort_order' => $index, 'is_default' => $index === 0]
            );
            return [$data['name'] => $loc->id];
        });

        // Productos demo
        $productsData = [
            [
                'name' => 'Leche entera 1L',
                'brand' => 'Sogar',
                'type' => 'Lácteos',
                'location' => 'Refrigerador',
                'unit_base' => 'l',
                'unit_size' => 1,
                'min_stock_qty' => 2,
                'qty' => 3,
            ],
            [
                'name' => 'Manzanas',
                'brand' => 'Granny',
                'type' => 'Verduras',
                'location' => 'Despensa',
                'unit_base' => 'kg',
                'unit_size' => 1,
                'min_stock_qty' => 1.5,
                'qty' => 2,
            ],
            [
                'name' => 'Detergente líquido',
                'brand' => 'Limpio',
                'type' => 'Aseo',
                'location' => 'Limpieza',
                'unit_base' => 'l',
                'unit_size' => 1,
                'min_stock_qty' => 1,
                'qty' => 1,
            ],
        ];

        $products = collect();
        foreach ($productsData as $data) {
            $product = FoodProduct::firstOrCreate(
                ['user_id' => $user->id, 'name' => $data['name']],
                [
                    'brand' => $data['brand'],
                    'type_id' => $types[$data['type']] ?? null,
                    'default_location_id' => $locations[$data['location']] ?? null,
                    'unit_base' => $data['unit_base'],
                    'unit_size' => $data['unit_size'],
                    'min_stock_qty' => $data['min_stock_qty'],
                    'presentation_qty' => $data['unit_size'],
                    'is_active' => true,
                ]
            );

            // Stock
            $existing = FoodStockBatch::where('product_id', $product->id)->sum('qty_remaining_base');
            if ($existing <= 0) {
                FoodStockBatch::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'location_id' => $product->default_location_id,
                    'qty_base' => $data['qty'],
                    'qty_remaining_base' => $data['qty'],
                    'unit_base' => $data['unit_base'],
                    'entered_on' => Carbon::today()->toDateString(),
                    'expires_on' => null,
                    'status' => 'ok',
                    'cost_total' => 0,
                    'currency' => 'USD',
                ]);
            }

            $products->push($product);
        }

        // Lista demo
        $list = ShoppingList::firstOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Compra Demo',
                'status' => 'active',
            ],
            [
                'family_group_id' => $family->id,
                'list_type' => 'food',
                'generated_at' => now(),
                'expected_purchase_on' => now()->addDays(3),
                'budget_id' => $budget->id,
                'category_id' => $category->id,
            ]
        );

        $sort = 0;
        foreach ($products as $product) {
            ShoppingListItem::firstOrCreate(
                [
                    'shopping_list_id' => $list->id,
                    'product_id' => $product->id,
                ],
                [
                    'name' => $product->name,
                    'qty_to_buy_base' => max(1, $product->min_stock_qty ?? 1),
                    'qty_suggested_base' => max(1, $product->min_stock_qty ?? 1),
                    'qty_current_base' => $product->batches()->sum('qty_remaining_base'),
                    'unit_base' => $product->unit_base,
                    'unit_size' => $product->unit_size,
                    'priority' => 'medium',
                    'sort_order' => $sort++,
                    'estimated_price' => 0,
                    'is_checked' => false,
                ]
            );
        }

        $this->command->info('✅ Datos demo de alimentos y lista de compras creados.');
        $this->command->info('👉 Usuario: william@sogar.com / S_07201* (admin sistema, familia demo activa).');
    }
}
