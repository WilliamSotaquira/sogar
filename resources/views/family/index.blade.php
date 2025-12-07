<x-layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Núcleos Familiares
            </h1>
            @if(auth()->user()->isSystemAdmin())
                <a href="{{ route('family.create') }}" 
                   aria-label="Crear un nuevo núcleo familiar"
                   class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Crear Núcleo Familiar
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Mensajes de éxito -->
            @if(session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 dark:bg-green-900/20" role="alert" aria-live="polite">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Grupo familiar activo -->
            @if($activeFamilyGroup)
                <section aria-labelledby="active-family-heading" class="mb-6 overflow-hidden bg-gradient-to-br from-indigo-50 to-white border-2 border-indigo-200 shadow-sm dark:from-gray-800 dark:to-gray-800 dark:border-indigo-900/50 sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 id="active-family-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Núcleo Familiar Activo
                            </h2>
                            <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1.5 text-xs font-semibold text-green-800 dark:bg-green-900/30 dark:text-green-300 ring-2 ring-green-600/20" role="status" aria-label="Estado: Activo">
                                <svg class="w-3 h-3 mr-1.5 animate-pulse" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <circle cx="10" cy="10" r="3"/>
                                </svg>
                                Activo
                            </span>
                        </div>
                        
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $activeFamilyGroup->name }}
                                </p>
                                @if($activeFamilyGroup->description)
                                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $activeFamilyGroup->description }}
                                    </p>
                                @endif
                                <div class="mt-3 flex items-center gap-4 text-sm">
                                    <span class="inline-flex items-center text-gray-600 dark:text-gray-400">
                                        <svg class="w-5 h-5 mr-1.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span class="font-semibold">{{ $activeFamilyGroup->members->count() }}</span>
                                        <span class="ml-1">{{ $activeFamilyGroup->members->count() === 1 ? 'miembro' : 'miembros' }}</span>
                                    </span>
                                    @if($activeFamilyGroup->admin)
                                        <span class="inline-flex items-center text-gray-600 dark:text-gray-400">
                                            <svg class="w-5 h-5 mr-1.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="font-medium">Admin:</span>
                                            <span class="ml-1">{{ $activeFamilyGroup->admin->name }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('family.show', $activeFamilyGroup) }}" 
                               aria-label="Ver detalles del núcleo familiar {{ $activeFamilyGroup->name }}"
                               class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Ver Detalles
                            </a>
                        </div>
                    </div>
                </section>
            @endif

            <!-- Lista de todos los núcleos familiares -->
            <section aria-labelledby="all-families-heading" class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6">
                    <h2 id="all-families-heading" class="mb-6 text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Todos mis Núcleos Familiares
                    </h2>

                    @if($familyGroups->isEmpty())
                        <div class="text-center py-12" role="status">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-gray-100">No hay núcleos familiares</h3>
                            @if(auth()->user()->isSystemAdmin())
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Comienza creando tu primer núcleo familiar para organizar tu familia.</p>
                                <div class="mt-6">
                                    <a href="{{ route('family.create') }}" 
                                       aria-label="Crear tu primer núcleo familiar"
                                       class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Crear Núcleo Familiar
                                    </a>
                                </div>
                            @else
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Aún no perteneces a ningún núcleo familiar. Contacta al administrador del sistema para que te agregue a uno.</p>
                            @endif
                        </div>
                    @else
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($familyGroups as $group)
                                <article class="relative overflow-hidden rounded-lg border-2 {{ $activeFamilyGroup && $activeFamilyGroup->id === $group->id ? 'border-indigo-500 bg-indigo-50/50 dark:border-indigo-500 dark:bg-indigo-900/20' : 'border-indigo-200 bg-white dark:border-indigo-800 dark:bg-gray-800' }} p-6 shadow-sm hover:shadow-lg transition-all duration-200 flex flex-col"
                                         aria-labelledby="family-{{ $group->id }}-name">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1 min-w-0">
                                            <h3 id="family-{{ $group->id }}-name" class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                                {{ $group->name }}
                                            </h3>
                                            @if($group->description)
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2" title="{{ $group->description }}">
                                                    {{ Str::limit($group->description, 80) }}
                                                </p>
                                            @endif
                                        </div>
                                        @if($activeFamilyGroup && $activeFamilyGroup->id === $group->id)
                                            <span class="ml-3 inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800 dark:bg-green-900/30 dark:text-green-300 shrink-0" role="status" aria-label="Este es tu núcleo familiar activo">
                                                <svg class="w-3 h-3 mr-1 animate-pulse" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                    <circle cx="10" cy="10" r="3"/>
                                                </svg>
                                                Activo
                                            </span>
                                        @endif
                                    </div>

                                    <div class="space-y-3 mb-5">
                                        <div class="flex items-center gap-2 text-sm font-medium text-gray-900 dark:text-white">
                                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            <span class="font-bold">{{ $group->members->count() }}</span>
                                            <span>{{ $group->members->count() === 1 ? 'miembro' : 'miembros' }}</span>
                                        </div>
                                        @if($group->admin)
                                            <div class="flex items-center gap-2 text-sm font-medium text-gray-900 dark:text-white">
                                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span class="font-semibold">Admin:</span>
                                                <span class="truncate">{{ $group->admin->name }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex gap-2 mt-auto">
                                        <a href="{{ route('family.show', $group) }}" 
                                           aria-label="Ver detalles del núcleo familiar {{ $group->name }}"
                                           class="flex-1 flex items-center justify-center gap-2 rounded-md bg-indigo-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Ver
                                        </a>
                                        @if(!$activeFamilyGroup || $activeFamilyGroup->id !== $group->id)
                                            <form action="{{ route('family.set-active', $group) }}" method="POST" class="flex-1">
                                                @csrf
                                                <button type="submit" 
                                                        aria-label="Activar núcleo familiar {{ $group->name }}"
                                                        class="w-full flex items-center justify-center gap-2 rounded-md bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600 dark:hover:bg-gray-600 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Activar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
