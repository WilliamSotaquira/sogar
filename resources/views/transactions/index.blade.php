@php
    $fmtMoney = fn ($value) => '$' . number_format($value, 0, ',', '.');
@endphp

<x-layouts.app :title="__('Transacciones')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="hero-panel p-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1.5">
                    <p class="text-sm font-semibold uppercase tracking-wide text-white/90">Historial</p>
                    <h1 class="text-3xl font-bold">Transacciones</h1>
                    <p class="text-sm text-white/85">Controla ingresos, gastos y bolsillos en un solo lugar.</p>
                    <div class="flex flex-wrap gap-2 pt-1 text-xs text-white/85">
                        <span class="hero-chip text-xs">Ingreso / Gasto</span>
                        <span class="hero-chip text-xs">Bolsillos asignados</span>
                        <span class="hero-chip text-xs">Neto por período</span>
                    </div>
                </div>
                <a
                    href="{{ route('transactions.create') }}"
                    class="relative inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-sm font-semibold text-slate-800 shadow-md ring-1 ring-white/60 transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    Registrar transacción
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-default bg-white p-5 shadow-sm dark:bg-neutral-900">
            <form method="GET" class="grid gap-4 md:grid-cols-4">
                <div>
                    <label class="block text-sm font-medium text-heading">Mes</label>
                    <input
                        type="number"
                        name="month"
                        min="1"
                        max="12"
                        value="{{ $filters['month'] }}"
                        class="mt-1 block h-12 w-full rounded-xl border border-default bg-white px-3 text-sm text-heading shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading">Año</label>
                    <input
                        type="number"
                        name="year"
                        min="2000"
                        max="2100"
                        value="{{ $filters['year'] }}"
                        class="mt-1 block h-12 w-full rounded-xl border border-default bg-white px-3 text-sm text-heading shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading">Categoría</label>
                    <select
                        name="category_id"
                        class="mt-1 block h-12 w-full rounded-xl border border-default bg-white px-3 text-sm text-heading shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white appearance-none"
                    >
                        <option value="">Todas</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($filters['category_id'] == $category->id)>
                                {{ ucfirst($category->type === 'income' ? 'Ingreso' : 'Gasto') }} · {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading">Bolsillo</label>
                    <select
                        name="wallet_id"
                        class="mt-1 block h-12 w-full rounded-xl border border-default bg-white px-3 text-sm text-heading shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white appearance-none"
                    >
                        <option value="">Todos</option>
                        @foreach ($wallets as $wallet)
                            <option value="{{ $wallet->id }}" @selected($filters['wallet_id'] == $wallet->id)>{{ $wallet->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 flex items-center justify-end gap-2">
                    <a href="{{ route('transactions.index') }}" class="text-sm text-gray-500 underline">Limpiar</a>
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                    >
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-default bg-white p-4 shadow-sm dark:bg-neutral-900">
                <p class="text-sm text-body">Ingresos</p>
                <h3 class="mt-2 text-2xl font-semibold text-emerald-600 dark:text-emerald-300">{{ $fmtMoney($totals['income']) }}</h3>
            </div>
            <div class="rounded-xl border border-default bg-white p-4 shadow-sm dark:bg-neutral-900">
                <p class="text-sm text-body">Gastos</p>
                <h3 class="mt-2 text-2xl font-semibold text-rose-600 dark:text-rose-300">{{ $fmtMoney($totals['expense']) }}</h3>
            </div>
            <div class="rounded-xl border border-default bg-white p-4 shadow-sm dark:bg-neutral-900">
                <p class="text-sm text-body">Neto</p>
                <h3 class="mt-2 text-2xl font-semibold text-heading dark:text-white">{{ $fmtMoney($totals['income'] - $totals['expense']) }}</h3>
            </div>
        </div>

        <div class="rounded-2xl border border-default bg-white p-4 shadow-sm dark:bg-neutral-900">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-heading dark:text-white">Movimientos</h2>
                <span class="text-xs text-body">{{ $transactions->total() }} resultados</span>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-default dark:divide-neutral-800">
                    <thead>
                        <tr class="text-left text-sm text-body">
                            <th class="px-4 py-2 font-semibold text-heading">Fecha</th>
                            <th class="px-4 py-2 font-semibold text-heading">Categoría</th>
                            <th class="px-4 py-2 font-semibold text-heading">Bolsillo</th>
                            <th class="px-4 py-2 text-right font-semibold text-heading">Monto</th>
                            <th class="px-4 py-2 font-semibold text-heading">Nota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-default dark:divide-neutral-800">
                        @forelse ($transactions as $transaction)
                            <tr class="text-sm text-heading dark:text-white">
                                <td class="px-4 py-2">{{ $transaction->occurred_on?->format('Y-m-d') }}</td>
                                <td class="px-4 py-2">
                                    @if($transaction->category)
                                        <span class="{{ $transaction->category->type === 'income' ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                                            {{ ucfirst($transaction->category->type === 'income' ? 'Ingreso' : 'Gasto') }}
                                        </span>
                                        · {{ $transaction->category->name }}
                                    @else
                                        Sin categoría
                                    @endif
                                </td>
                                <td class="px-4 py-2">{{ $transaction->wallet?->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-right">
                                    @php
                                        $isIncome = $transaction->category?->type === 'income';
                                        $sign = $isIncome ? '+' : '-';
                                    @endphp
                                    <span class="{{ $isIncome ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                                        {{ $sign }}{{ $fmtMoney($transaction->amount) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">{{ $transaction->note ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">No hay transacciones en este período.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
