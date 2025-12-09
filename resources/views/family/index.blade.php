@php
    $btnPrimary = 'inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500';
    $btnSecondary = 'inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-gray-100 dark:hover:bg-neutral-700';
    $btnDanger = 'inline-flex items-center justify-center rounded-lg border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-800 dark:bg-neutral-900 dark:text-rose-400 dark:hover:bg-rose-900/20';
@endphp

<x-layouts.app>
    <div class="mx-auto w-full max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-8 shadow-lg dark:from-emerald-600 dark:to-teal-700">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="text-white">
                    <p class="text-xs font-semibold uppercase tracking-wide opacity-90">Gestión Familiar</p>
                    <h1 class="mt-1 text-3xl font-bold">Núcleos Familiares</h1>
                    <p class="mt-2 text-sm opacity-90">Organiza y administra los miembros de tu familia</p>
                </div>
                @if(auth()->user()->isSystemAdmin())
                    <a href="{{ route('family.create') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-emerald-700 shadow-lg transition hover:bg-gray-50">
                        <span class="text-base">➕</span>
                        <span>Crear Núcleo Familiar</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Mensajes de éxito --}}
        @if(session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <p class="font-semibold text-emerald-900 dark:text-emerald-100">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Grupo familiar activo --}}
        @if($activeFamilyGroup)
            <div class="rounded-lg border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-6 shadow-sm dark:border-emerald-800 dark:from-emerald-950/40 dark:to-teal-950/40">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="flex-1">
                        <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                            Activo
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $activeFamilyGroup->name }}</h2>
                        @if($activeFamilyGroup->description)
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $activeFamilyGroup->description }}</p>
                        @endif
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-2 rounded-lg bg-white/80 px-3 py-1.5 text-sm font-medium text-blue-700 shadow-sm dark:bg-blue-900/30 dark:text-blue-200">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ $activeFamilyGroup->members->count() }} {{ Str::plural('miembro', $activeFamilyGroup->members->count()) }}
                            </span>
                            @if($activeFamilyGroup->admin)
                                <span class="inline-flex items-center gap-2 rounded-lg bg-white/80 px-3 py-1.5 text-sm font-medium text-amber-700 shadow-sm dark:bg-amber-900/30 dark:text-amber-200">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Admin: {{ $activeFamilyGroup->admin->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('family.show', $activeFamilyGroup) }}" class="{{ $btnPrimary }}">
                        Ver detalles
                    </a>
                </div>
            </div>
        @endif

        {{-- Lista de todos los núcleos familiares --}}
        <div class="space-y-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">
                    Resumen general
                </p>
                <h2 class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Núcleos Familiares
                </h2>
            </div>

            @if($familyGroups->isEmpty())
                <div class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 py-16 text-center dark:border-neutral-700 dark:bg-neutral-900/40">
                    <div class="mx-auto mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-neutral-800">
                        <span class="text-4xl">👨‍👩‍👧‍👦</span>
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">No hay núcleos familiares</h3>
                    @if(auth()->user()->isSystemAdmin())
                        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Comienza creando tu primer núcleo familiar para organizar tu familia</p>
                        <a href="{{ route('family.create') }}" class="{{ $btnPrimary }}">
                            <span class="text-base">➕</span>
                            <span>Crear Núcleo Familiar</span>
                        </a>
                    @else
                        <p class="text-sm text-gray-600 dark:text-gray-400">Aún no perteneces a ningún núcleo familiar. Contacta al administrador del sistema para que te agregue a uno.</p>
                    @endif
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($familyGroups as $group)
                        @php
                            $isActive = $activeFamilyGroup && $activeFamilyGroup->id === $group->id;
                            $canManage = auth()->user()->isAdminOfFamilyGroup($group->id) || auth()->user()->isSystemAdmin();
                        @endphp
                        <div class="rounded-lg border p-5 shadow-sm transition {{ $isActive ? 'border-emerald-300 bg-emerald-50/50 dark:border-emerald-700 dark:bg-emerald-950/20' : 'border-gray-200 bg-white dark:border-neutral-800 dark:bg-neutral-900' }}">
                            <div class="mb-4">
                                <div class="mb-3 flex items-start justify-between gap-3">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Familia</p>
                                    @if($isActive)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 dark:text-emerald-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                            Activo
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ $group->name }}
                                </h3>
                                @if($group->description)
                                    <p class="mt-1 line-clamp-2 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $group->description }}
                                    </p>
                                @endif
                            </div>

                            <div class="mb-4 space-y-2">
                                <div class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm dark:bg-neutral-800">
                                    <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span class="font-medium text-gray-700 dark:text-gray-200">
                                        {{ $group->members->count() }} {{ Str::plural('miembro', $group->members->count()) }}
                                    </span>
                                </div>
                                @if($group->admin)
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm dark:bg-neutral-800">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-medium text-gray-700 dark:text-gray-200">
                                            Admin: {{ $group->admin->name }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-2">
                                <a href="{{ route('family.show', $group) }}" class="{{ $btnPrimary }} w-full">
                                    Ver detalles
                                </a>
                                <div class="grid grid-cols-2 gap-2">
                                    @if(!$isActive)
                                        <form action="{{ route('family.set-active', $group) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="{{ $btnSecondary }} w-full text-xs">
                                                Activar
                                            </button>
                                        </form>
                                    @endif
                                    @if($canManage)
                                        <a href="{{ route('family.edit', $group) }}" class="{{ $btnSecondary }} text-xs {{ !$isActive ? '' : 'col-span-2' }}">
                                            Editar
                                        </a>
                                    @endif
                                </div>
                                @if(auth()->user()->isSystemAdmin() && $canManage)
                                    <form action="{{ route('family.destroy', $group) }}" method="POST" onsubmit="return confirm('¿Eliminar este núcleo familiar? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="{{ $btnDanger }} w-full text-xs">
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
