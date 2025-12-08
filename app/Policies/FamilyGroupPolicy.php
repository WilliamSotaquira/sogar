<?php

namespace App\Policies;

use App\Models\FamilyGroup;
use App\Models\User;

class FamilyGroupPolicy
{
    /**
     * Cualquier usuario autenticado puede listar sus propios grupos.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user;
    }

    /**
     * Ver un grupo: debe ser miembro o admin del sistema.
     */
    public function view(User $user, FamilyGroup $familyGroup): bool
    {
        return $user->isSystemAdmin() || $user->belongsToFamilyGroup($familyGroup->id);
    }

    /**
     * Solo los administradores del sistema pueden crear nuevos núcleos.
     */
    public function create(User $user): bool
    {
        return $user->isSystemAdmin();
    }

    /**
     * Actualizar datos generales: admin del grupo o admin del sistema.
     */
    public function update(User $user, FamilyGroup $familyGroup): bool
    {
        return $user->isSystemAdmin() || $user->isAdminOfFamilyGroup($familyGroup->id);
    }

    /**
     * Activar un grupo: cualquier miembro puede activar el suyo.
     */
    public function setActive(User $user, FamilyGroup $familyGroup): bool
    {
        return $user->belongsToFamilyGroup($familyGroup->id);
    }

    /**
     * Gestionar miembros: admin del grupo o admin del sistema.
     */
    public function manageMembers(User $user, FamilyGroup $familyGroup): bool
    {
        return $user->isSystemAdmin() || $user->isAdminOfFamilyGroup($familyGroup->id);
    }

    /**
     * Eliminar un núcleo: admin del sistema o admin del grupo.
     */
    public function delete(User $user, FamilyGroup $familyGroup): bool
    {
        return $user->isSystemAdmin() || $user->isAdminOfFamilyGroup($familyGroup->id);
    }
}
