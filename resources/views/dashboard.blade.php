@php
    $fmtMoney = fn ($value) => '$' . number_format($value, 0, ',', '.');
@endphp

<x-layouts.app :title="__('Dashboard')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="hero-panel p-6">
            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="hero-panel-content text-white">
                    <p class="text-sm uppercase tracking-wide font-semibold text-white/90">Finanzas familiares</p>
                    <h1 class="mt-1 text-3xl font-bold">Visión y control en un solo lugar</h1>
                    <p class="mt-2 text-sm text-white/85">Captura, presupuestos, bolsillos y alertas conectadas a Google Calendar.</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('transactions.create') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-md ring-1 ring-white/60 transition hover:-translate-y-0.5 hover:shadow-lg">
                            Registrar transacción
                        </a>
                        <a href="{{ route('budgets.index') }}" class="inline-flex items-center gap-2 rounded-full border border-white/40 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10">
                            Presupuestos
                        </a>
                        <a href="{{ route('recurrences.index') }}" class="inline-flex items-center gap-2 rounded-full border border-white/40 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10">
                            Recurrencias
                        </a>
                        <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-2 rounded-full border border-white/40 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10">
                            Transacciones
                        </a>
                        <a href="{{ route('wallets.index') }}" class="inline-flex items-center gap-2 rounded-full border border-white/40 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10">
                            Bolsillos
                        </a>
                    </div>
                </div>
                <div class="hero-panel-content flex flex-col gap-3 text-sm text-white lg:items-end">
                    <div class="flex items-center gap-2 rounded-full bg-white/12 px-3 py-1 font-semibold ring-1 ring-white/15">
                        <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                        Salud: {{ $healthScore }}/100
                    </div>
                    <div class="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                        <p class="text-xs text-white/80">Ahorro proyectado a 6 meses</p>
                        <p class="text-xl font-bold">{{ $fmtMoney($projectedSavings) }}</p>
                    </div>
                    @if($googleIntegration)
                        <form method="POST" action="{{ route('integrations.google.disconnect') }}" class="inline-flex">
                            @csrf @method('DELETE')
                            <button class="rounded-full border border-white/30 px-3 py-2 font-semibold hover:bg-white/10">Desconectar Google</button>
                        </form>
                    @else
                        <a href="{{ route('integrations.google.redirect') }}" class="inline-flex items-center gap-2 rounded-full border border-white/30 px-3 py-2 font-semibold hover:bg-white/10">
                            Conectar Google
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-default bg-white p-4 shadow-md dark:bg-neutral-900">
                <p class="text-sm text-body">Ingresos del mes</p>
                <h3 class="mt-2 text-2xl font-semibold text-heading dark:text-white">{{ $fmtMoney($income) }}</h3>
                <p class="text-xs text-emerald-600 dark:text-emerald-300">Incluye salarios y extras.</p>
            </div>
            <div class="rounded-xl border border-default bg-white p-4 shadow-md dark:bg-neutral-900">
                <p class="text-sm text-body">Gastos del mes</p>
                <h3 class="mt-2 text-2xl font-semibold text-heading dark:text-white">{{ $fmtMoney($expenses) }}</h3>
                <p class="text-xs text-rose-500 dark:text-rose-300">Controla límites y fugas.</p>
            </div>
            <div class="rounded-xl border border-default bg-white p-4 shadow-md dark:bg-neutral-900">
                <p class="text-sm text-body">% Ahorro vs ingreso</p>
                <h3 class="mt-2 text-2xl font-semibold text-heading dark:text-white">{{ round($savingsRate * 100, 1) }}%</h3>
                <p class="text-xs text-body">Meta ideal: 20% o más.</p>
            </div>
            <div class="rounded-xl border border-default bg-white p-4 shadow-md dark:bg-neutral-900">
                <p class="text-sm text-body">Salud financiera</p>
                <div class="mt-2 flex items-center gap-3">
                    <div class="h-2 flex-1 rounded-full bg-neutral-100 dark:bg-neutral-800">
                        <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $healthScore }}%"></div>
                    </div>
                    <span class="text-lg font-semibold text-heading dark:text-white">{{ $healthScore }}</span>
                </div>
                <p class="text-xs text-body">Menos alertas, más score.</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Presupuestos del mes</h2>
                        <span class="text-xs text-gray-500">{{ now()->translatedFormat('F Y') }}</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($budgets as $budget)
                            <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-50">{{ $budget['category'] }}</p>
                                        <p class="text-xs text-gray-500">Gastado: {{ $fmtMoney($budget['spent']) }} de {{ $fmtMoney($budget['amount']) }}</p>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ $budget['percent'] }}%</span>
                                </div>
                                <div class="mt-2 h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800">
                                    <div
                                        class="h-2 rounded-full bg-emerald-500"
                                        style="width: {{ $budget['percent'] }}%"
                                    ></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Aún no hay presupuestos para este mes.</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Bolsillos</h2>
                        <span class="text-xs text-gray-500">Saldo actual</span>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        @forelse ($wallets as $wallet)
                            <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-50">{{ $wallet['name'] }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $wallet['is_shared'] ? 'Compartido' : 'Personal' }}
                                            @if($wallet['target'])
                                                · Meta: {{ $fmtMoney($wallet['target']) }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-300">{{ $fmtMoney($wallet['balance']) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Aún no hay bolsillos creados.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Alertas</h2>
                    <div class="mt-3 space-y-3">
                        @forelse ($alerts as $alert)
                            <div class="rounded-lg border border-amber-100 bg-amber-50 p-3 text-amber-800 dark:border-amber-900/50 dark:bg-amber-900/30 dark:text-amber-100">
                                <p class="text-sm font-semibold">{{ $alert['title'] }}</p>
                                <p class="text-xs">{{ $alert['message'] }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Sin alertas por ahora. ¡Buen trabajo!</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Proyección de ahorro</h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Si mantienes este ritmo, en 6 meses:</p>
                    <p class="mt-3 text-2xl font-semibold text-emerald-600 dark:text-emerald-300">{{ $fmtMoney($projectedSavings) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ajusta presupuestos o reduce gastos para mejorar.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
