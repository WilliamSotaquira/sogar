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
        $williamEmail = env('SOGAR_SEED_WILLIAM_EMAIL', 'william@example.com');
        $williamPassword = env('SOGAR_SEED_WILLIAM_PASSWORD', 'password');

        $jazminEmail = env('SOGAR_SEED_JAZMIN_EMAIL', 'jazmin@example.com');
        $jazminPassword = env('SOGAR_SEED_JAZMIN_PASSWORD', 'password');

        $santiagoEmail = env('SOGAR_SEED_SANTIAGO_EMAIL', 'santiago@example.com');
        $santiagoPassword = env('SOGAR_SEED_SANTIAGO_PASSWORD', 'password');

        // Crear usuario William
        User::updateOrCreate(
            ['email' => $williamEmail],
            [
                'name' => 'William',
                'password' => Hash::make($williamPassword),
                'email_verified_at' => now(),
                'is_system_admin' => true,
            ]
        );

        // Crear usuario Esposa
        User::updateOrCreate(
            ['email' => $jazminEmail],
            [
                'name' => 'Jazmin',
                'password' => Hash::make($jazminPassword),
                'email_verified_at' => now(),
            ]
        );

        // Crear usuario Hijo
        User::updateOrCreate(
            ['email' => $santiagoEmail],
            [
                'name' => 'Santiago',
                'password' => Hash::make($santiagoPassword),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Usuarios seed creados/actualizados.');
        $this->command->info("👤 William: {$williamEmail} (password via SOGAR_SEED_WILLIAM_PASSWORD)");
        $this->command->info("👩 Jazmin: {$jazminEmail} (password via SOGAR_SEED_JAZMIN_PASSWORD)");
        $this->command->info("👦 Santiago: {$santiagoEmail} (password via SOGAR_SEED_SANTIAGO_PASSWORD)");

    }
}
