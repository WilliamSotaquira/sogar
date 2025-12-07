<?php

namespace App\Http\Controllers;

use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FamilyGroupController extends Controller
{
    /**
     * Display a listing of family groups
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener todos los grupos familiares del usuario
        $familyGroups = $user->familyGroups()->with(['admin', 'members'])->get();
        
        // Obtener el grupo familiar activo
        $activeFamilyGroup = $user->activeFamilyGroup;
        
        return view('family.index', compact('familyGroups', 'activeFamilyGroup'));
    }

    /**
     * Show the form for creating a new family group
     */
    public function create()
    {
        // Solo administradores del sistema pueden crear núcleos familiares
        if (!Auth::user()->isSystemAdmin()) {
            abort(403, 'Solo los administradores del sistema pueden crear núcleos familiares');
        }

        return view('family.create');
    }

    /**
     * Store a newly created family group
     */
    public function store(Request $request)
    {
        // Solo administradores del sistema pueden crear núcleos familiares
        if (!Auth::user()->isSystemAdmin()) {
            abort(403, 'Solo los administradores del sistema pueden crear núcleos familiares');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Crear el grupo familiar
        $familyGroup = FamilyGroup::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'admin_user_id' => $user->id,
            'is_active' => true,
        ]);

        // Agregar al usuario como administrador del grupo
        FamilyMember::create([
            'family_group_id' => $familyGroup->id,
            'user_id' => $user->id,
            'role' => 'padre', // O 'madre' según corresponda
            'is_admin' => true,
            'can_manage_finances' => true,
            'can_manage_food' => true,
            'can_manage_shopping' => true,
            'joined_at' => now(),
        ]);

        // Si el usuario no tiene un grupo familiar activo, establecer este como activo
        if (!$user->active_family_group_id) {
            $user->update(['active_family_group_id' => $familyGroup->id]);
        }

        return redirect()->route('family.show', $familyGroup)
            ->with('success', 'Núcleo familiar creado exitosamente');
    }

    /**
     * Display the specified family group
     */
    public function show(FamilyGroup $familyGroup)
    {
        $user = Auth::user();

        // Verificar que el usuario pertenezca al grupo familiar
        if (!$user->belongsToFamilyGroup($familyGroup->id)) {
            abort(403, 'No tienes acceso a este núcleo familiar');
        }

        // Cargar relaciones
        $familyGroup->load(['members', 'admin', 'familyMembers.user']);

        return view('family.show', compact('familyGroup'));
    }

    /**
     * Show the form for editing the family group
     */
    public function edit(FamilyGroup $familyGroup)
    {
        $user = Auth::user();

        // Solo los administradores pueden editar
        if (!$user->isAdminOfFamilyGroup($familyGroup->id)) {
            abort(403, 'No tienes permisos para editar este núcleo familiar');
        }

        return view('family.edit', compact('familyGroup'));
    }

    /**
     * Update the specified family group
     */
    public function update(Request $request, FamilyGroup $familyGroup)
    {
        $user = Auth::user();

        // Solo los administradores pueden actualizar
        if (!$user->isAdminOfFamilyGroup($familyGroup->id)) {
            abort(403, 'No tienes permisos para actualizar este núcleo familiar');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $familyGroup->update($validated);

        return redirect()->route('family.show', $familyGroup)
            ->with('success', 'Núcleo familiar actualizado exitosamente');
    }

    /**
     * Set a family group as the active one for the user
     */
    public function setActive(FamilyGroup $familyGroup)
    {
        $user = Auth::user();

        // Verificar que el usuario pertenezca al grupo familiar
        if (!$user->belongsToFamilyGroup($familyGroup->id)) {
            abort(403, 'No perteneces a este núcleo familiar');
        }

        $user->update(['active_family_group_id' => $familyGroup->id]);

        return redirect()->back()
            ->with('success', 'Núcleo familiar activo cambiado exitosamente');
    }

    /**
     * Add a member to the family group
     */
    public function addMember(Request $request, FamilyGroup $familyGroup)
    {
        $user = Auth::user();

        // Solo los administradores del sistema pueden agregar miembros
        if (!$user->isSystemAdmin()) {
            abort(403, 'Solo los administradores del sistema pueden agregar miembros');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:padre,madre,hijo,hija,otro',
            'is_admin' => 'boolean',
            'can_manage_finances' => 'boolean',
            'can_manage_food' => 'boolean',
            'can_manage_shopping' => 'boolean',
        ]);

        // Verificar que el usuario no sea ya miembro
        if ($familyGroup->hasMember($validated['user_id'])) {
            return redirect()->back()
                ->with('error', 'Este usuario ya es miembro del núcleo familiar');
        }

        FamilyMember::create([
            'family_group_id' => $familyGroup->id,
            'user_id' => $validated['user_id'],
            'role' => $validated['role'],
            'is_admin' => $validated['is_admin'] ?? false,
            'can_manage_finances' => $validated['can_manage_finances'] ?? false,
            'can_manage_food' => $validated['can_manage_food'] ?? false,
            'can_manage_shopping' => $validated['can_manage_shopping'] ?? false,
            'joined_at' => now(),
        ]);

        return redirect()->route('family.show', $familyGroup)
            ->with('success', 'Miembro agregado exitosamente');
    }

    /**
     * Update a member's permissions
     */
    public function updateMember(Request $request, FamilyGroup $familyGroup, FamilyMember $member)
    {
        $user = Auth::user();

        // Solo los administradores pueden actualizar permisos
        if (!$user->isAdminOfFamilyGroup($familyGroup->id)) {
            abort(403, 'No tienes permisos para actualizar miembros');
        }

        $validated = $request->validate([
            'role' => 'required|in:padre,madre,hijo,hija,otro',
            'is_admin' => 'boolean',
            'can_manage_finances' => 'boolean',
            'can_manage_food' => 'boolean',
            'can_manage_shopping' => 'boolean',
        ]);

        $member->update($validated);

        return redirect()->route('family.show', $familyGroup)
            ->with('success', 'Permisos del miembro actualizados exitosamente');
    }

    /**
     * Remove a member from the family group
     */
    public function removeMember(FamilyGroup $familyGroup, $memberId)
    {
        $user = Auth::user();

        // Solo los administradores pueden remover miembros
        if (!$user->isAdminOfFamilyGroup($familyGroup->id)) {
            abort(403, 'No tienes permisos para remover miembros');
        }

        // Buscar el miembro dentro del family group
        $member = $familyGroup->familyMembers()->where('id', $memberId)->first();
        
        if (!$member) {
            return redirect()->back()
                ->with('error', 'Miembro no encontrado');
        }

        // No se puede remover al administrador principal
        if ($member->user_id === $familyGroup->admin_user_id) {
            return redirect()->back()
                ->with('error', 'No se puede remover al administrador principal del núcleo familiar');
        }

        $member->delete();

        return redirect()->route('family.show', $familyGroup)
            ->with('success', 'Miembro removido exitosamente');
    }
}
