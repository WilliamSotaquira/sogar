@php
    $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
    $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
@endphp

<x-layouts.app>
    <div class="mx-auto w-full max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="hero-panel p-8">
            <div class="hero-panel-content flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold text-white/90">Gestión Familiar</p>
                    <h1 class="text-4xl font-bold mt-1">Núcleos Familiares</h1>
                    <p class="text-base text-white/80 mt-2">Organiza y administra los miembros de tu familia</p>
                </div>
                @if(auth()->user()->isSystemAdmin())
                    <a href="{{ route('family.create') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/95 text-emerald-700 px-5 py-2.5 text-sm font-semibold shadow-lg hover:bg-white transition">
                        ➕ Crear Núcleo Familiar
                    </a>
                @endif
            </div>
        </div>

        {{-- Mensajes de éxito --}}
        @if(session('success'))
            <div class="rounded-xl bg-emerald-50/80 p-4 border border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-lg bg-emerald-500 flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <p class="text-base font-semibold text-emerald-900 dark:text-emerald-100">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Grupo familiar activo --}}
        @if($activeFamilyGroup)
            <div class="rounded-2xl border border-emerald-100/70 bg-white/90 p-6 shadow-lg shadow-emerald-50 backdrop-blur-sm dark:border-white/10 dark:bg-gray-900/70">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.3em] text-emerald-600 uppercase">Activo</p>
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $activeFamilyGroup->name }}</h2>
                        @if($activeFamilyGroup->description)
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $activeFamilyGroup->description }}</p>
                        @endif
                        <div class="mt-4 flex flex-wrap gap-3 text-sm">
                            <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ $activeFamilyGroup->members->count() }} {{ Str::plural('miembro', $activeFamilyGroup->members->count()) }}
                            </span>
                            @if($activeFamilyGroup->admin)
                                <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Admin: {{ $activeFamilyGroup->admin->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('family.show', $activeFamilyGroup) }}" class="{{ $btnPrimary }} text-sm">
                        Ver detalles
                    </a>
                </div>
            </div>
        @endif

        {{-- Lista de todos los núcleos familiares --}}
        <div class="space-y-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-500 dark:text-emerald-300">
                    Resumen general
                </p>
                <h2 class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Núcleos Familiares
                </h2>
            </div>

            @if($familyGroups->isEmpty())
                <div class="text-center py-16 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-white/80 dark:bg-gray-900/40">
                    <div class="inline-flex h-20 w-20 rounded-full bg-gray-100 dark:bg-gray-800 items-center justify-center mb-5">
                        <span class="text-5xl">👨‍👩‍👧‍👦</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">No hay núcleos familiares</h3>
                    @if(auth()->user()->isSystemAdmin())
                        <p class="text-base text-gray-600 dark:text-gray-400 mb-6">Comienza creando tu primer núcleo familiar para organizar tu familia</p>
                        <a href="{{ route('family.create') }}"
                           class="{{ $btnPrimary }} text-base">
                            ➕ Crear Núcleo Familiar
                        </a>
                    @else
                        <p class="text-base text-gray-600 dark:text-gray-400">Aún no perteneces a ningún núcleo familiar. Contacta al administrador del sistema para que te agregue a uno.</p>
                    @endif
                </div>
            @else
                <div class="grid gap-5 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    @foreach($familyGroups as $group)
                        <div class="rounded-2xl p-5 shadow-lg ring-1 ring-inset transition {{ $activeFamilyGroup && $activeFamilyGroup->id === $group->id ? 'border border-emerald-200/70 bg-gradient-to-br from-white via-emerald-50/70 to-white dark:border-emerald-500/40 dark:bg-emerald-950/40 dark:ring-emerald-500/20' : 'border border-gray-200/70 bg-white/95 dark:border-white/5 dark:bg-slate-900/60 dark:ring-white/10' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0 space-y-2">
                                    <p class="text-[0.7rem] uppercase tracking-[0.4em] text-gray-400 dark:text-gray-500">Familia</p>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $group->name }}
                                        </h3>
                                        @if($group->description)
                                            <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                                                {{ $group->description }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                @if($activeFamilyGroup && $activeFamilyGroup->id === $group->id)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-200">
                                        <span class="h-2 w-2 rounded-full bg-current"></span>
                                        Activo
                                    </span>
                                @endif
                            </div>

                            <div class="mt-5 flex flex-col gap-2 text-sm text-gray-700 dark:text-gray-200">
                                <div class="inline-flex items-center gap-2 rounded-xl border border-gray-100/70 bg-gray-50/80 px-3 py-1.5 text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    {{ $group->members->count() }} {{ Str::plural('miembro', $group->members->count()) }}
                                </div>
                                @if($group->admin)
                                    <div class="inline-flex items-center gap-2 rounded-xl border border-gray-100/70 bg-gray-50/80 px-3 py-1.5 text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Admin: {{ $group->admin->name }}
                                    </div>
                                @endif
                            </div>

                            <div class="mt-6 flex gap-3">
                                <a href="{{ route('family.show', $group) }}" class="{{ $btnPrimary }} flex-1 justify-center text-sm">
                                    Ver detalles
                                </a>
                                @if(!$activeFamilyGroup || $activeFamilyGroup->id !== $group->id)
                                    <form action="{{ route('family.set-active', $group) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" class="{{ $btnSecondary }} w-full justify-center text-sm">
                                            Activar
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
