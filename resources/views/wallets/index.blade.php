@php
    $fmtMoney = fn ($value) => '$' . number_format($value, 0, ',', '.');
@endphp

<x-layouts.app :title="__('Bolsillos')">
    <div class="space-y-6">
        <div class="rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 p-6 text-white shadow-lg">
            <p class="text-sm uppercase tracking-wide font-semibold">Fondos separados</p>
            <h1 class="text-3xl font-bold">Bolsillos</h1>
            <p class="text-sm text-white/85">Monitorea saldos, metas y registra ajustes manuales.</p>
            <div class="mt-3 flex flex-wrap gap-2 text-xs text-white/85">
                <span class="rounded-full bg-white/15 px-3 py-1">Compartidos/Personales</span>
                <span class="rounded-full bg-white/15 px-3 py-1">Movimientos recientes</span>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($wallets as $item)
                @php
                    $wallet = $item['model'];
                    $balance = $item['balance'];
                @endphp
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $wallet->name }}</h2>
                            <p class="text-xs text-gray-500">
                                {{ $wallet->is_shared ? 'Compartido' : 'Personal' }}
                                @if($wallet->target_amount)
                                    · Meta: {{ $fmtMoney($wallet->target_amount) }}
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Saldo</p>
                            <p class="text-xl font-semibold text-emerald-600 dark:text-emerald-300">{{ $fmtMoney($balance) }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <form method="POST" action="{{ route('wallets.movements.store', $wallet) }}" class="grid gap-3 md:grid-cols-3">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monto (+/-)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="amount"
                                    class="mt-1 form-field"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha</label>
                                <input
                                    type="date"
                                    name="occurred_on"
                                    value="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 form-field"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Concepto</label>
                                <input
                                    type="text"
                                    name="concept"
                                    class="mt-1 form-field"
                                    placeholder="Ajuste manual"
                                >
                            </div>
                            <div class="md:col-span-3 flex justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                >
                                    Registrar movimiento
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="mt-4">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Movimientos recientes</h3>
                        <div class="mt-2 divide-y divide-gray-100 border border-gray-100 dark:divide-gray-800 dark:border-gray-800 rounded-lg">
                            @forelse ($item['movements'] as $movement)
                                <div class="flex items-center justify-between px-3 py-2 text-sm text-gray-800 dark:text-gray-100">
                                    <div>
                                        <p class="font-medium">{{ $movement->concept ?? 'Ajuste' }}</p>
                                        <p class="text-xs text-gray-500">{{ $movement->occurred_on?->format('Y-m-d') }}</p>
                                    </div>
                                    <span class="{{ $movement->amount >= 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                                        {{ $movement->amount >= 0 ? '+' : '' }}{{ $fmtMoney($movement->amount) }}
                                    </span>
                                </div>
                            @empty
                                <p class="px-3 py-2 text-xs text-gray-500">Sin movimientos aún.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No hay bolsillos aún.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
