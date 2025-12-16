@php
    $fmtMoney = fn ($value) => '$' . number_format($value, 0, ',', '.');
@endphp

<x-layouts.app :title="__('Dashboard')">
    <div id="dashboard-content" class="mx-auto w-full max-w-6xl space-y-6">
        <a href="#dashboard-content" class="sr-only focus:not-sr-only focus:rounded-lg focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-semibold focus:text-gray-900 focus:ring-2 focus:ring-emerald-400 dark:focus:bg-neutral-900 dark:focus:text-gray-50">
            Saltar al contenido principal
        </a>

        {{-- Encabezado / contexto --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-50">
                    Dashboard
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Un hub para Finanzas, Alimentos y Familia.
                </p>
                @if($activeFamilyGroup)
                    <p class="mt-2 inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">
                        Núcleo activo: {{ $activeFamilyGroup->name }}
                    </p>
                @endif
            </div>

            <div class="flex flex-col items-start gap-2 md:items-end">
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-200 dark:ring-emerald-800/60">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Salud: {{ $healthScore }}/100
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('transactions.create') }}"
                       class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                        Registrar transacción
                    </a>
                    <a href="{{ route('food.shopping-list.index') }}"
                       class="inline-flex items-center justify-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-800 dark:bg-neutral-900 dark:text-gray-100 dark:hover:bg-neutral-800">
                        Lista de compras
                    </a>
                    <a href="{{ route('food.inventory.index') }}"
                       class="inline-flex items-center justify-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-800 dark:bg-neutral-900 dark:text-gray-100 dark:hover:bg-neutral-800">
                        Inventario
                    </a>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    @if($googleIntegration)
                        <form method="POST" action="{{ route('integrations.google.disconnect') }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="underline hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:hover:text-gray-200">
                                Google Calendar: conectado (desconectar)
                            </button>
                        </form>
                    @else
                        <a href="{{ route('integrations.google.redirect') }}" class="underline hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:hover:text-gray-200">
                            Google Calendar: conectar
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Módulos --}}
        <section aria-labelledby="modules-title" class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 id="modules-title" class="text-lg font-semibold text-gray-900 dark:text-gray-50">
                    Módulos
                </h2>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                {{-- Finanzas --}}
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-50">Finanzas</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ingresos, gastos, presupuestos y bolsillos.</p>
                        </div>
                        <a href="{{ route('transactions.index') }}"
                           class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-emerald-300 dark:hover:text-emerald-200">
                            Abrir
                        </a>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-500">Ingresos (mes)</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $fmtMoney($income) }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-500">Gastos (mes)</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $fmtMoney($expenses) }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-500">Balance (mes)</dt>
                            <dd class="mt-1 text-lg font-semibold {{ $netThisMonth >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                                {{ $fmtMoney($netThisMonth) }}
                            </dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-500">Presupuestos en riesgo</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $budgetsAtRiskCount }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('transactions.create') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                            Registrar
                        </a>
                        <a href="{{ route('budgets.index') }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-800 dark:bg-neutral-900 dark:text-gray-100 dark:hover:bg-neutral-800">
                            Presupuestos
                        </a>
                        <a href="{{ route('wallets.index') }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-800 dark:bg-neutral-900 dark:text-gray-100 dark:hover:bg-neutral-800">
                            Bolsillos ({{ $activeWalletsCount }})
                        </a>
                        <a href="{{ route('recurrences.index') }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-800 dark:bg-neutral-900 dark:text-gray-100 dark:hover:bg-neutral-800">
                            Recurrencias
                        </a>
                    </div>
                </div>

                {{-- Alimentos --}}
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-50">Alimentos</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Inventario, compras, productos y alertas.</p>
                        </div>
                        <a href="{{ route('food.inventory.index') }}"
                           class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-emerald-300 dark:hover:text-emerald-200">
                            Abrir
                        </a>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-500">Productos</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $foodProductsCount }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-500">Stock bajo</dt>
                            <dd class="mt-1 text-lg font-semibold {{ $foodLowStockCount > 0 ? 'text-rose-600 dark:text-rose-300' : 'text-gray-900 dark:text-gray-50' }}">
                                {{ $foodLowStockCount }}
                            </dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-500">Caduca pronto (7 días)</dt>
                            <dd class="mt-1 text-lg font-semibold {{ $foodExpiringSoonCount > 0 ? 'text-amber-700 dark:text-amber-200' : 'text-gray-900 dark:text-gray-50' }}">
                                {{ $foodExpiringSoonCount }}
                            </dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-500">Listas activas</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $foodActiveListsCount }}</dd>
                        </div>
                    </dl>

                    @if($foodLatestActiveList)
                        <div class="mt-3 rounded-lg border border-gray-200 bg-white p-3 text-sm dark:border-gray-800 dark:bg-neutral-900">
                            <p class="text-xs font-medium text-gray-500">Lista activa</p>
                            <div class="mt-1 flex items-center justify-between gap-3">
                                <a href="{{ route('food.shopping-list.show', $foodLatestActiveList) }}"
                                   class="font-semibold text-gray-900 underline decoration-gray-300 underline-offset-2 hover:text-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-gray-50 dark:hover:text-emerald-200">
                                    {{ $foodLatestActiveList->name }}
                                </a>
                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                    Pendientes: {{ $foodLatestActiveList->pending_items_count ?? 0 }}
                                </span>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('food.shopping-list.index') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                            Lista
                        </a>
                        <a href="{{ route('food.products.index') }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-800 dark:bg-neutral-900 dark:text-gray-100 dark:hover:bg-neutral-800">
                            Productos
                        </a>
                        <a href="{{ route('food.purchases.index') }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-800 dark:bg-neutral-900 dark:text-gray-100 dark:hover:bg-neutral-800">
                            Compras
                        </a>
                        <a href="{{ route('food.locations.index') }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-800 dark:bg-neutral-900 dark:text-gray-100 dark:hover:bg-neutral-800">
                            Ubicaciones
                        </a>
                    </div>
                </div>

                {{-- Familia --}}
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-50">Familia</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Núcleo, miembros y permisos.</p>
                        </div>
                        <a href="{{ route('family.index') }}"
                           class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-emerald-300 dark:hover:text-emerald-200">
                            Abrir
                        </a>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-500">Núcleo activo</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-50">
                                {{ $activeFamilyGroup?->name ?? 'No configurado' }}
                            </dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-500">Miembros</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-50">
                                {{ $activeFamilyGroup?->members_count ?? 0 }}
                            </dd>
                        </div>
                        <div class="col-span-2 rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-500">Accesos</dt>
                            <dd class="mt-1 text-sm text-gray-700 dark:text-gray-200">
                                Administra quién puede ver Finanzas y Alimentos.
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('family.index') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                            Mi núcleo
                        </a>
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-800 dark:bg-neutral-900 dark:text-gray-100 dark:hover:bg-neutral-800">
                            Ajustes
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Alertas --}}
        <section aria-labelledby="alerts-title" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3">
                <h2 id="alerts-title" class="text-lg font-semibold text-gray-900 dark:text-gray-50">
                    Alertas
                </h2>
            </div>
            <div class="mt-3 space-y-3">
                @forelse ($alerts as $alert)
                    <div class="rounded-lg border border-amber-100 bg-amber-50 p-3 text-amber-900 dark:border-amber-900/50 dark:bg-amber-900/30 dark:text-amber-50">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold">
                                    {{ $alert['title'] }}
                                </p>
                                <p class="mt-1 text-xs">
                                    {{ $alert['message'] }}
                                </p>
                            </div>
                            @if(!empty($alert['route']))
                                <a href="{{ $alert['route'] }}"
                                   class="shrink-0 rounded-lg bg-white/70 px-3 py-2 text-xs font-semibold text-amber-900 transition hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:bg-amber-950/40 dark:text-amber-50 dark:hover:bg-amber-950/60">
                                    Ver
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">
                        Sin alertas por ahora. ¡Buen trabajo!
                    </p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
