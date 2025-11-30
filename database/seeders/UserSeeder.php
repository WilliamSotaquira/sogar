<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario William (tú)
        User::create([
            'name' => 'William',
            'email' => 'william@sogar.com',
            'password' => Hash::make('S_07201*'),
            'email_verified_at' => now(),
        ]);

        // Crear usuario Esposa
        User::create([
            'name' => 'Jazmin',
            'email' => 'Jazmin@sogar.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
        ]);

        // Crear usuario Hijo
        User::create([
            'name' => 'Santiago',
            'email' => 'Santiago@sogar.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Usuarios de Sogar creados exitosamente!');
        $this->command->info('👤 William: william@sogar.com / 123456');
        $this->command->info('👩 Esposa: Jazmin@sogar.com / 123456');
        $this->command->info('👦 Hijo: Santiago@sogar.com / 123456');
    }
}
