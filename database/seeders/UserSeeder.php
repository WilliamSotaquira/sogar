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
        User::updateOrCreate(
            ['email' => 'william@sogar.com'],
            [
                'name' => 'William',
                'password' => Hash::make('S_07201*'),
                'email_verified_at' => now(),
            ]
        );

        // Crear usuario Esposa
        User::updateOrCreate(
            ['email' => 'Jazmin@sogar.com'],
            [
                'name' => 'Jazmin',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
            ]
        );

        // Crear usuario Hijo
        User::updateOrCreate(
            ['email' => 'Santiago@sogar.com'],
            [
                'name' => 'Santiago',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Usuarios de Sogar creados exitosamente!');
        $this->command->info('👤 William: william@sogar.com / S_07201*');
        $this->command->info('👩 Esposa: Jazmin@sogar.com / 123456');
        $this->command->info('👦 Hijo: Santiago@sogar.com / 123456');
    }
}
