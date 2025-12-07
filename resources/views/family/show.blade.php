<x-layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('family.index') }}" 
                   class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ $familyGroup->name }}
                </h2>
                @if(auth()->user()->active_family_group_id === $familyGroup->id)
                    <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        Activo
                    </span>
                @endif
            </div>
            @if(auth()->user()->isAdminOfFamilyGroup($familyGroup->id))
                <a href="{{ route('family.edit', $familyGroup) }}" 
                   class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Mensajes -->
            @if(session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 dark:bg-green-900/20">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-200">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 dark:bg-red-900/20">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="ml-3 text-sm font-medium text-red-800 dark:text-red-200">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Información del grupo -->
                <div class="lg:col-span-2">
                    <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                Información del Núcleo Familiar
                            </h3>
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $familyGroup->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Administrador</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $familyGroup->admin->name }}</dd>
                                </div>
                                @if($familyGroup->description)
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $familyGroup->description }}</dd>
                                    </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                                    <dd class="mt-1">
                                        @if($familyGroup->is_active)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">
                                                Inactivo
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Miembros</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $familyGroup->members->count() }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Miembros del núcleo familiar -->
                    <div class="mt-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Miembros
                                </h3>
                                @if(auth()->user()->isSystemAdmin())
                                    <button type="button" 
                                            onclick="openAddModal()"
                                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Agregar Miembro
                                    </button>
                                @endif
                            </div>

                            <div class="space-y-4">
                                @foreach($familyGroup->familyMembers as $member)
                                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                        <div class="flex items-center gap-4">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/30">
                                                <span class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">
                                                    {{ $member->user->initials() }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ $member->user->name }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $member->role_label }}
                                                    @if($member->is_admin)
                                                        · <span class="text-indigo-600 dark:text-indigo-400">Administrador</span>
                                                    @endif
                                                </p>
                                                <div class="mt-1 flex flex-wrap gap-1">
                                                    @if($member->can_manage_finances)
                                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                            💰 Finanzas
                                                        </span>
                                                    @endif
                                                    @if($member->can_manage_food)
                                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                            🍽️ Alimentos
                                                        </span>
                                                    @endif
                                                    @if($member->can_manage_shopping)
                                                        <span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                                            🛒 Compras
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @if(auth()->user()->isAdminOfFamilyGroup($familyGroup->id) && $member->user_id !== $familyGroup->admin_user_id)
                                            <div class="flex gap-2">
                                                <button type="button" 
                                                        onclick="editMember({{ $member->id }}, '{{ $member->role }}', {{ $member->is_admin ? 'true' : 'false' }}, {{ $member->can_manage_finances ? 'true' : 'false' }}, {{ $member->can_manage_food ? 'true' : 'false' }}, {{ $member->can_manage_shopping ? 'true' : 'false' }})"
                                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <form action="{{ route('family.members.remove', [$familyGroup->id, $member->id]) }}" 
                                                      method="POST" 
                                                      class="inline delete-member-form"
                                                      data-member-name="{{ $member->user->name }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            aria-label="Remover a {{ $member->user->name }}"
                                                            class="p-1 text-red-600 hover:text-red-900 hover:bg-red-50 rounded dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/10 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="space-y-6">
                    @if(auth()->user()->active_family_group_id !== $familyGroup->id)
                        <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    Acciones
                                </h3>
                                <form action="{{ route('family.set-active', $familyGroup) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        Establecer como Activo
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                Tu Rol
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ auth()->user()->getRoleInFamilyGroup($familyGroup->id) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Miembro -->
    <div id="addMemberModal" 
         class="hidden fixed inset-0 z-50" 
         style="background-color: rgba(0,0,0,0.5);"
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="addMemberModalTitle"
         aria-describedby="addMemberModalDescription">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full" 
                 onclick="event.stopPropagation();"
                 role="document">
                <form action="{{ route('family.members.add', $familyGroup) }}" method="POST" id="addMemberForm">
                    @csrf
                    <div class="p-6">
                        <h3 id="addMemberModalTitle" class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Agregar Miembro al Núcleo Familiar
                        </h3>
                        <p id="addMemberModalDescription" class="sr-only">
                            Formulario para agregar un nuevo miembro al núcleo familiar. Los campos marcados con asterisco son obligatorios.
                        </p>
                        
                        <div class="space-y-4 mt-4">
                            <!-- Usuario -->
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Usuario <span class="text-red-600" aria-label="obligatorio">*</span>
                                </label>
                                <select name="user_id" 
                                        id="user_id" 
                                        required
                                        aria-required="true"
                                        aria-label="Seleccione el usuario que desea agregar al núcleo familiar"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">Seleccionar usuario...</option>
                                    @foreach(\App\Models\User::whereNotIn('id', $familyGroup->members->pluck('id'))->get() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Rol -->
                            <div>
                                <label for="add_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Rol <span class="text-red-600" aria-label="obligatorio">*</span>
                                </label>
                                <select name="role" 
                                        id="add_role" 
                                        required
                                        aria-required="true"
                                        aria-label="Seleccione el rol del miembro en la familia"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="padre">Padre</option>
                                    <option value="madre">Madre</option>
                                    <option value="hijo">Hijo</option>
                                    <option value="hija">Hija</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <!-- Permisos -->
                            <fieldset>
                                <legend class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Permisos (opcional)
                                </legend>
                                <div class="space-y-3" role="group" aria-label="Permisos del miembro">
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               name="is_admin" 
                                               id="add_is_admin"
                                               value="1" 
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                                        <label for="add_is_admin" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                            Administrador del grupo
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               name="can_manage_finances" 
                                               id="add_can_manage_finances"
                                               value="1" 
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                                        <label for="add_can_manage_finances" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                            <span aria-hidden="true">💰</span> Gestionar finanzas
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               name="can_manage_food" 
                                               id="add_can_manage_food"
                                               value="1" 
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                                        <label for="add_can_manage_food" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                            <span aria-hidden="true">🍽️</span> Gestionar alimentos
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               name="can_manage_shopping" 
                                               id="add_can_manage_shopping"
                                               value="1" 
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                                        <label for="add_can_manage_shopping" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                            <span aria-hidden="true">🛒</span> Gestionar compras
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900 px-6 py-3 flex justify-end gap-3">
                        <button type="button" 
                                id="addMemberCancelBtn"
                                onclick="closeAddModal()"
                                aria-label="Cancelar y cerrar el modal"
                                class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                            Cancelar
                        </button>
                        <button type="submit"
                                id="addMemberSubmitBtn"
                                aria-label="Agregar miembro al núcleo familiar"
                                class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Agregar Miembro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Miembro -->
    <div id="editMemberModal" 
         class="hidden fixed inset-0 z-50" 
         style="background-color: rgba(0,0,0,0.5);"
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="editMemberModalTitle"
         aria-describedby="editMemberModalDescription">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full" 
                 onclick="event.stopPropagation();"
                 role="document">
                <form id="editMemberForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-6">
                        <h3 id="editMemberModalTitle" class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Editar Permisos del Miembro
                        </h3>
                        <p id="editMemberModalDescription" class="sr-only">
                            Formulario para editar el rol y permisos de un miembro del núcleo familiar.
                        </p>
                        
                        <div class="space-y-4 mt-4">
                            <!-- Rol -->
                            <div>
                                <label for="edit_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Rol <span class="text-red-600" aria-label="obligatorio">*</span>
                                </label>
                                <select name="role" 
                                        id="edit_role" 
                                        required
                                        aria-required="true"
                                        aria-label="Seleccione el rol del miembro en la familia"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="padre">Padre</option>
                                    <option value="madre">Madre</option>
                                    <option value="hijo">Hijo</option>
                                    <option value="hija">Hija</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <!-- Permisos -->
                            <fieldset>
                                <legend class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Permisos (opcional)
                                </legend>
                                <div class="space-y-3" role="group" aria-label="Permisos del miembro">
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               name="is_admin" 
                                               id="edit_is_admin" 
                                               value="1" 
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                                        <label for="edit_is_admin" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                            Administrador del grupo
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               name="can_manage_finances" 
                                               id="edit_can_manage_finances" 
                                               value="1" 
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                                        <label for="edit_can_manage_finances" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                            <span aria-hidden="true">💰</span> Gestionar finanzas
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               name="can_manage_food" 
                                               id="edit_can_manage_food" 
                                               value="1" 
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                                        <label for="edit_can_manage_food" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                            <span aria-hidden="true">🍽️</span> Gestionar alimentos
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               name="can_manage_shopping" 
                                               id="edit_can_manage_shopping" 
                                               value="1" 
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                                        <label for="edit_can_manage_shopping" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                            <span aria-hidden="true">🛒</span> Gestionar compras
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900 px-6 py-3 flex justify-end gap-3">
                        <button type="button" 
                                id="editMemberCancelBtn"
                                onclick="closeEditModal()"
                                aria-label="Cancelar y cerrar el modal"
                                class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                            Cancelar
                        </button>
                        <button type="submit"
                                id="editMemberSubmitBtn"
                                aria-label="Guardar cambios del miembro"
                                class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Variables para gestionar el foco anterior
        let previousActiveElement = null;

        // Funciones para abrir/cerrar modales con focus management
        function openAddModal() {
            previousActiveElement = document.activeElement;
            const modal = document.getElementById('addMemberModal');
            modal.classList.remove('hidden');
            setTimeout(() => {
                const firstInput = document.getElementById('user_id');
                if (firstInput) firstInput.focus();  
            }, 100);
        }

        function closeAddModal() {
            document.getElementById('addMemberModal').classList.add('hidden');
            if (previousActiveElement) previousActiveElement.focus();
        }

        function openEditModal() {
            previousActiveElement = document.activeElement;
            const modal = document.getElementById('editMemberModal');
            modal.classList.remove('hidden');
            setTimeout(() => {
                const firstInput = document.getElementById('edit_role');
                if (firstInput) firstInput.focus();
            }, 100);
        }

        function closeEditModal() {
            document.getElementById('editMemberModal').classList.add('hidden');
            if (previousActiveElement) previousActiveElement.focus();
        }

        function editMember(memberId, role, isAdmin, canManageFinances, canManageFood, canManageShopping) {
            const form = document.getElementById('editMemberForm');
            form.action = '{{ route("family.members.update", [$familyGroup, ":memberId"]) }}'.replace(':memberId', memberId);
            
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_is_admin').checked = isAdmin;
            document.getElementById('edit_can_manage_finances').checked = canManageFinances;
            document.getElementById('edit_can_manage_food').checked = canManageFood;
            document.getElementById('edit_can_manage_shopping').checked = canManageShopping;
            
            openEditModal();
        }

        // Event listeners para cerrar modales al hacer clic en el backdrop
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar modal
            const addModal = document.getElementById('addMemberModal');
            addModal.addEventListener('click', function(e) {
                if (e.target === addModal) {
                    closeAddModal();
                }
            });

            // Editar modal
            const editModal = document.getElementById('editMemberModal');
            editModal.addEventListener('click', function(e) {
                if (e.target === editModal) {
                    closeEditModal();
                }
            });

            // Cerrar con tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAddModal();
                    closeEditModal();
                }
            });

            // Manejo de confirmación para eliminar miembros
            document.querySelectorAll('.delete-member-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const memberName = this.dataset.memberName;
                    const confirmed = confirm(`¿Estás seguro de que deseas remover a ${memberName}?`);
                    if (!confirmed) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</x-layouts.app>