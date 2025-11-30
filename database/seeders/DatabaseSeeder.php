<?php

namespace Database\Seeders;

use Database\Seeders\FinanceSeeder;
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
            FinanceSeeder::class,
        ]);
    }
}
