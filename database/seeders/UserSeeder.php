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
            ['email' => 'william.sotaquira@gmail.com'],
            [
                'name' => 'William',
                'password' => Hash::make('S0t4qu1r4.2025*'),
                'email_verified_at' => now(),
                'is_system_admin' => true,
            ]
        );

        // Crear usuario Esposa
        User::updateOrCreate(
            ['email' => 'valeria920309@gmail.com'],
            [
                'name' => 'Jazmin',
                'password' => Hash::make('1012386506'),
                'email_verified_at' => now(),
            ]
        );

        // Crear usuario Hijo
        User::updateOrCreate(
            ['email' => 'santiago.sotaquira.suarez@gmail.com'],
            [
                'name' => 'Santiago',
                'password' => Hash::make('1013127679'),
                'email_verified_at' => now(),
            ]
        );



        $this->command->info('✅ Usuarios de Sogar creados exitosamente!');
        $this->command->info('👤 William: william.sotaquira@gmail.com / S0t4qu1r4.2025*');
        $this->command->info('👩 Jazmin: valeria920309@gmail.com / 1012386506');
        $this->command->info('👦 Santiago: santiago.sotaquira.suarez@gmail.com / 1013127679');

    }
}
