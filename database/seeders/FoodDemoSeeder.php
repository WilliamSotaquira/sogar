<?php

namespace Database\Seeders;

use App\Models\FoodLocation;
use App\Models\FoodPrice;
use App\Models\FoodProduct;
use App\Models\FoodStockBatch;
use App\Models\FoodType;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FoodDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $this->command?->warn('FoodDemoSeeder: no users found, skipping.');
            return;
        }

        // Ubicaciones básicas
        $locations = collect([
            ['name' => 'Despensa', 'slug' => 'despensa', 'color' => '#6EE7B7'],
            ['name' => 'Nevera', 'slug' => 'nevera', 'color' => '#93C5FD'],
            ['name' => 'Congelador', 'slug' => 'congelador', 'color' => '#A5B4FC'],
        ])->mapWithKeys(function ($loc, $i) use ($user) {
            $location = FoodLocation::updateOrCreate(
                ['user_id' => $user->id, 'slug' => $loc['slug']],
                [
                    'name' => $loc['name'],
                    'color' => $loc['color'],
                    'sort_order' => $i,
                    'is_default' => $i === 0,
                ],
            );
            return [$loc['slug'] => $location->id];
        });

        // Tipos de alimentos
        $types = collect([
            ['name' => 'Granos', 'slug' => 'granos'],
            ['name' => 'Lácteos', 'slug' => 'lacteos'],
            ['name' => 'Proteínas', 'slug' => 'proteinas'],
        ])->mapWithKeys(function ($type, $i) use ($user) {
            $t = FoodType::updateOrCreate(
                ['user_id' => $user->id, 'name' => $type['name']],
                [
                    'description' => $type['name'],
                    'color' => '#'.Str::padLeft(dechex(rand(0, 0xFFFFFF)), 6, '0'),
                    'sort_order' => $i,
                    'is_active' => true,
                ],
            );
            return [$type['slug'] => $t->id];
        });

        // Productos de ejemplo
        $productsData = [
            [
                'name' => 'Arroz blanco 1kg',
                'brand' => 'Diana',
                'barcode' => '7701000000016',
                'unit_base' => 'g',
                'unit_size' => 1000,
                'min_stock_qty' => 500,
                'shelf_life_days' => 365,
                'type_slug' => 'granos',
                'location_slug' => 'despensa',
                'price_per_base' => 0.0055,
                'stock' => 1200,
            ],
            [
                'name' => 'Leche entera 1L',
                'brand' => 'Alquería',
                'barcode' => '7702000000007',
                'unit_base' => 'ml',
                'unit_size' => 1000,
                'min_stock_qty' => 2000,
                'shelf_life_days' => 20,
                'type_slug' => 'lacteos',
                'location_slug' => 'nevera',
                'price_per_base' => 0.0042,
                'stock' => 3000,
            ],
            [
                'name' => 'Huevos docena',
                'brand' => 'Kikes',
                'barcode' => '7703000000008',
                'unit_base' => 'unit',
                'unit_size' => 1,
                'min_stock_qty' => 6,
                'shelf_life_days' => 25,
                'type_slug' => 'proteinas',
                'location_slug' => 'nevera',
                'price_per_base' => 0.45,
                'stock' => 18,
            ],
            [
                'name' => 'Pechuga de pollo 500g',
                'brand' => 'Superpollo',
                'barcode' => '7704000000009',
                'unit_base' => 'g',
                'unit_size' => 500,
                'min_stock_qty' => 500,
                'shelf_life_days' => 180,
                'type_slug' => 'proteinas',
                'location_slug' => 'congelador',
                'price_per_base' => 0.009,
                'stock' => 800,
            ],
        ];

        $products = [];
        foreach ($productsData as $data) {
            $product = FoodProduct::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'barcode' => $data['barcode'],
                ],
                [
                    'name' => $data['name'],
                    'brand' => $data['brand'],
                    'unit_base' => $data['unit_base'],
                    'unit_size' => $data['unit_size'],
                    'min_stock_qty' => $data['min_stock_qty'],
                    'shelf_life_days' => $data['shelf_life_days'],
                    'type_id' => $types[$data['type_slug']] ?? null,
                    'default_location_id' => $locations[$data['location_slug']] ?? null,
                    'is_active' => true,
                ],
            );

            FoodPrice::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'price_per_base' => $data['price_per_base'],
                    'currency' => 'COP',
                    'source' => 'demo',
                    'captured_on' => Carbon::now()->toDateString(),
                ],
            );

            FoodStockBatch::updateOrCreate(
                ['product_id' => $product->id, 'user_id' => $user->id],
                [
                    'location_id' => $locations[$data['location_slug']] ?? null,
                    'qty_base' => $data['stock'],
                    'qty_remaining_base' => $data['stock'],
                    'unit_base' => $data['unit_base'],
                    'status' => 'ok',
                    'entered_on' => Carbon::now()->subDays(3)->toDateString(),
                    'expires_on' => Carbon::now()->addDays($data['shelf_life_days'])->toDateString(),
                ],
            );

            $products[] = $product;
        }

        // Lista activa demo si no existe
        $active = ShoppingList::where('user_id', $user->id)->where('status', 'active')->latest()->first();
        if (!$active) {
            $active = ShoppingList::create([
                'user_id' => $user->id,
                'name' => 'DEMO-' . now()->format('YmdHis'),
                'status' => 'active',
                'generated_at' => now(),
                'expected_purchase_on' => now()->addDays(7),
                'people_count' => 3,
                'purchase_frequency_days' => 7,
                'safety_factor' => 1.2,
            ]);

            foreach ($products as $idx => $product) {
                $currentStock = FoodStockBatch::where('product_id', $product->id)->sum('qty_remaining_base');
                $need = max(($product->min_stock_qty ?? 0) * 2 - $currentStock, 0);

                ShoppingListItem::create([
                    'shopping_list_id' => $active->id,
                    'name' => $product->name,
                    'product_id' => $product->id,
                    'priority' => $idx === 0 ? 'high' : 'medium',
                    'qty_suggested_base' => $need ?: $product->unit_size,
                    'qty_current_base' => $currentStock,
                    'qty_to_buy_base' => $need ?: $product->unit_size,
                    'qty_unit_label' => $product->unit_size . ' ' . $product->unit_base,
                    'unit_base' => $product->unit_base,
                    'unit_size' => $product->unit_size,
                    'estimated_price' => ($product->prices()->latest()->value('price_per_base') ?? 0) * ($need ?: $product->unit_size),
                    'is_checked' => false,
                    'barcode' => $product->barcode,
                    'sort_order' => $idx,
                    'metadata' => json_encode(['demo' => true]),
                ]);
            }

            $active->update([
                'estimated_budget' => $active->items()->sum('estimated_price'),
            ]);
        }
    }
}
