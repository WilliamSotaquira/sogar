@php
    $fmtMoney = fn ($value) => '$' . number_format($value, 0, ',', '.');
@endphp

<x-layouts.app :title="__('Dashboard')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Dashboard financiero</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">Visión rápida de ingresos, gastos, bolsillos y alertas.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a
                    href="{{ route('transactions.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                >
                    Registrar transacción
                </a>
                <a
                    href="{{ route('budgets.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-800 dark:focus:ring-offset-gray-900"
                >
                    Presupuestos
                </a>
                <a
                    href="{{ route('recurrences.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-800 dark:focus:ring-offset-gray-900"
                >
                    Recurrencias
                </a>
                <div class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Salud: {{ $healthScore }}/100
                </div>
                <div class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    Ahorro proyectado 6m: {{ $fmtMoney($projectedSavings) }}
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500">Ingresos del mes</p>
                <h3 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-50">{{ $fmtMoney($income) }}</h3>
                <p class="text-xs text-emerald-600 dark:text-emerald-300">Incluye salarios y extras.</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500">Gastos del mes</p>
                <h3 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-50">{{ $fmtMoney($expenses) }}</h3>
                <p class="text-xs text-rose-500 dark:text-rose-300">Controla límites y fugas.</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500">% Ahorro vs ingreso</p>
                <h3 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-50">{{ round($savingsRate * 100, 1) }}%</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Meta ideal: 20% o más.</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500">Salud financiera</p>
                <div class="mt-2 flex items-center gap-3">
                    <div class="h-2 flex-1 rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $healthScore }}%"></div>
                    </div>
                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $healthScore }}</span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Menos alertas, más score.</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Presupuestos del mes</h2>
                        <span class="text-xs text-gray-500">{{ now()->format('F Y') }}</span>
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
