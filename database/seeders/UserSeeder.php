<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production') && !env('SOGAR_SEED_ALLOW_PROD', false)) {
            $this->command?->warn('⛔ UserSeeder: se omite en producción. Define SOGAR_SEED_ALLOW_PROD=true si realmente deseas ejecutarlo.');
            return;
        }

        $resetPasswords = (bool) env('SOGAR_SEED_RESET_PASSWORDS', false);

        $williamEmail = env('SOGAR_SEED_WILLIAM_EMAIL', 'william@example.com');
        $williamPassword = env('SOGAR_SEED_WILLIAM_PASSWORD', 'password');
        $williamName = env('SOGAR_SEED_WILLIAM_NAME', 'William');

        $jazminEmail = env('SOGAR_SEED_JAZMIN_EMAIL', 'jazmin@example.com');
        $jazminPassword = env('SOGAR_SEED_JAZMIN_PASSWORD', 'password');
        $jazminName = env('SOGAR_SEED_JAZMIN_NAME', 'Jazmin');

        $santiagoEmail = env('SOGAR_SEED_SANTIAGO_EMAIL', 'santiago@example.com');
        $santiagoPassword = env('SOGAR_SEED_SANTIAGO_PASSWORD', 'password');
        $santiagoName = env('SOGAR_SEED_SANTIAGO_NAME', 'Santiago');

        $william = $this->seedUser(
            email: $williamEmail,
            name: $williamName,
            password: $williamPassword,
            resetPasswords: $resetPasswords,
            extra: ['is_system_admin' => true]
        );

        $jazmin = $this->seedUser(
            email: $jazminEmail,
            name: $jazminName,
            password: $jazminPassword,
            resetPasswords: $resetPasswords,
        );

        $santiago = $this->seedUser(
            email: $santiagoEmail,
            name: $santiagoName,
            password: $santiagoPassword,
            resetPasswords: $resetPasswords,
        );

        $this->command->info('✅ Usuarios seed creados/actualizados.');
        $this->command->info("👤 {$william->name}: {$william->email} (password via SOGAR_SEED_WILLIAM_PASSWORD" . ($resetPasswords ? '; reset=1' : '') . ")");
        $this->command->info("👩 {$jazmin->name}: {$jazmin->email} (password via SOGAR_SEED_JAZMIN_PASSWORD" . ($resetPasswords ? '; reset=1' : '') . ")");
        $this->command->info("👦 {$santiago->name}: {$santiago->email} (password via SOGAR_SEED_SANTIAGO_PASSWORD" . ($resetPasswords ? '; reset=1' : '') . ")");

    }

    /**
     * Crea o actualiza sin pisar password a menos que se solicite.
     */
    private function seedUser(string $email, string $name, string $password, bool $resetPasswords, array $extra = []): User
    {
        $normalizedEmail = Str::lower(trim($email));

        $user = User::query()->firstOrNew(['email' => $normalizedEmail]);
        $isNew = !$user->exists;

        $user->name = $name;
        $user->email = $normalizedEmail;

        if ($isNew || $resetPasswords) {
            $user->password = Hash::make($password);
        }

        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
        }

        foreach ($extra as $key => $value) {
            $user->{$key} = $value;
        }

        $user->save();

        return $user;
    }
}
