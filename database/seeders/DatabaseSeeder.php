<?php

namespace Database\Seeders;

use Database\Seeders\FinanceSeeder;
use Database\Seeders\FoodDemoSeeder;
use Database\Seeders\FoodTypeSeeder;
use Database\Seeders\FoodLocationSeeder;
use Database\Seeders\FamilyGroupSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            FamilyGroupSeeder::class,
            FinanceSeeder::class,
            FoodTypeSeeder::class,
            FoodLocationSeeder::class,
            FoodDemoSeeder::class,
        ]);
    }
}
