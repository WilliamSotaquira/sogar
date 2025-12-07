<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FamilyGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar usuarios existentes
        $padre = User::where('email', 'william@sogar.com')->first();
        $madre = User::where('email', 'Jazmin@sogar.com')->first();
        $hijo = User::where('email', 'Santiago@sogar.com')->first();

        if (!$padre || !$madre || !$hijo) {
            $this->command->error('❌ No se encontraron todos los usuarios necesarios. Ejecuta primero UserSeeder.');
            return;
        }

        // Crear el núcleo familiar
        $familyGroup = FamilyGroup::updateOrCreate(
            ['name' => 'Familia Sotaquirá'],
            [
                'description' => 'Núcleo familiar principal',
                'admin_user_id' => $padre->id,
                'is_active' => true,
            ]
        );

        // Agregar al padre como administrador con todos los permisos
        FamilyMember::updateOrCreate(
            [
                'family_group_id' => $familyGroup->id,
                'user_id' => $padre->id,
            ],
            [
                'role' => 'padre',
                'is_admin' => true,
                'can_manage_finances' => true,
                'can_manage_food' => true,
                'can_manage_shopping' => true,
                'joined_at' => now(),
            ]
        );

        // Agregar a la madre con permisos completos
        FamilyMember::updateOrCreate(
            [
                'family_group_id' => $familyGroup->id,
                'user_id' => $madre->id,
            ],
            [
                'role' => 'madre',
                'is_admin' => true,
                'can_manage_finances' => true,
                'can_manage_food' => true,
                'can_manage_shopping' => true,
                'joined_at' => now(),
            ]
        );

        // Agregar al hijo con permisos limitados
        FamilyMember::updateOrCreate(
            [
                'family_group_id' => $familyGroup->id,
                'user_id' => $hijo->id,
            ],
            [
                'role' => 'hijo',
                'is_admin' => false,
                'can_manage_finances' => false,
                'can_manage_food' => true,
                'can_manage_shopping' => true,
                'joined_at' => now(),
            ]
        );

        // Establecer este grupo familiar como activo para todos los miembros
        $padre->update(['active_family_group_id' => $familyGroup->id]);
        $madre->update(['active_family_group_id' => $familyGroup->id]);
        $hijo->update(['active_family_group_id' => $familyGroup->id]);

        $this->command->info('✓ Núcleo familiar creado exitosamente');
        $this->command->info('✓ Padre: ' . $padre->name . ' (' . $padre->email . ')');
        $this->command->info('✓ Madre: ' . $madre->name . ' (' . $madre->email . ')');
        $this->command->info('✓ Hijo: ' . $hijo->name . ' (' . $hijo->email . ')');
    }
}
