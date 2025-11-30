@php
    $fmtMoney = fn ($value) => '$' . number_format($value, 0, ',', '.');
@endphp

<x-layouts.app :title="__('Transacciones')">
    <div class="space-y-6">
        <div class="rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-500 to-violet-500 p-6 shadow-lg text-white">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Historial</p>
                    <h1 class="text-3xl font-bold">Transacciones</h1>
                    <p class="text-sm text-white/85">Filtra por periodo, categoría o bolsillo y controla tus movimientos.</p>
                </div>
                <a
                    href="{{ route('transactions.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-white/90 px-4 py-2 text-sm font-semibold text-blue-700 shadow hover:bg-white"
                >
                    Registrar transacción
                </a>
            </div>
            <div class="mt-3 flex flex-wrap gap-2 text-xs text-white/85">
                <span class="rounded-full bg-white/15 px-3 py-1">Ingreso/Gasto</span>
                <span class="rounded-full bg-white/15 px-3 py-1">Bolsillos asignados</span>
                <span class="rounded-full bg-white/15 px-3 py-1">Neto por período</span>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form method="GET" class="grid gap-4 md:grid-cols-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mes</label>
                    <input
                        type="number"
                        name="month"
                        min="1"
                        max="12"
                        value="{{ $filters['month'] }}"
                        class="mt-1 form-field"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Año</label>
                    <input
                        type="number"
                        name="year"
                        min="2000"
                        max="2100"
                        value="{{ $filters['year'] }}"
                        class="mt-1 form-field"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                    <select
                        name="category_id"
                        class="mt-1 select-field"
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bolsillo</label>
                    <select
                        name="wallet_id"
                        class="mt-1 select-field"
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
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500">Ingresos</p>
                <h3 class="mt-2 text-2xl font-semibold text-emerald-600 dark:text-emerald-300">{{ $fmtMoney($totals['income']) }}</h3>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500">Gastos</p>
                <h3 class="mt-2 text-2xl font-semibold text-rose-600 dark:text-rose-300">{{ $fmtMoney($totals['expense']) }}</h3>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500">Neto</p>
                <h3 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-50">{{ $fmtMoney($totals['income'] - $totals['expense']) }}</h3>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Movimientos</h2>
                <span class="text-xs text-gray-500">{{ $transactions->total() }} resultados</span>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-sm text-gray-600 dark:text-gray-400">
                            <th class="px-4 py-2">Fecha</th>
                            <th class="px-4 py-2">Categoría</th>
                            <th class="px-4 py-2">Bolsillo</th>
                            <th class="px-4 py-2 text-right">Monto</th>
                            <th class="px-4 py-2">Nota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($transactions as $transaction)
                            <tr class="text-sm text-gray-800 dark:text-gray-100">
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
