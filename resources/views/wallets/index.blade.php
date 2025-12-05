@php
    $fmtMoney = fn ($value) => '$' . number_format($value, 0, ',', '.');
@endphp

<x-layouts.app :title="__('Bolsillos')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="hero-panel p-6">
            <p class="text-sm uppercase tracking-wide font-semibold text-white/90">Fondos separados</p>
            <h1 class="text-3xl font-bold text-white">Bolsillos</h1>
            <p class="text-sm text-white/85">Monitorea saldos, metas y registra ajustes manuales.</p>
            <div class="mt-3 flex flex-wrap gap-2 text-xs text-white/85">
                <span class="hero-chip">Compartidos/Personales</span>
                <span class="hero-chip">Movimientos recientes</span>
                <span class="hero-chip">Activa/Inactiva para bloquear movimientos</span>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 dark:border-rose-900/50 dark:bg-rose-900/30 dark:text-rose-100">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-50">
                            {{ $editingWallet ? 'Editar bolsillo' : 'Nuevo bolsillo' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Define saldos iniciales, metas y si es compartido.
                        </p>
                    </div>
                    @if ($editingWallet)
                        <a href="{{ route('wallets.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-300 dark:hover:text-amber-200">
                            Crear nuevo
                        </a>
                    @endif
                </div>

                <form
                    method="POST"
                    action="{{ $editingWallet ? route('wallets.update', $editingWallet) : route('wallets.store') }}"
                    class="mt-4 space-y-4"
                >
                    @csrf
                    @if ($editingWallet)
                        @method('PUT')
                    @endif

                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $editingWallet->name ?? '') }}"
                            class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="Ej. Ahorro casa, Viajes"
                            required
                        >
                        @error('name')
                            <p class="text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Saldo inicial</label>
                            <input
                                type="number"
                                step="0.01"
                                name="initial_balance"
                                value="{{ old('initial_balance', $editingWallet->initial_balance ?? 0) }}"
                                class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                required
                            >
                            @error('initial_balance')
                                <p class="text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta (opcional)</label>
                            <input
                                type="number"
                                step="0.01"
                                name="target_amount"
                                value="{{ old('target_amount', $editingWallet->target_amount ?? '') }}"
                                class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                placeholder="Ej. 5000000"
                            >
                            @error('target_amount')
                                <p class="text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción (opcional)</label>
                        <input
                            type="text"
                            name="description"
                            value="{{ old('description', $editingWallet->description ?? '') }}"
                            class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="Ayuda a tu pareja o equipo"
                        >
                        @error('description')
                            <p class="text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input
                                type="checkbox"
                                name="is_shared"
                                value="1"
                                class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800"
                                @checked(old('is_shared', $editingWallet->is_shared ?? false))
                            >
                            Compartido
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800"
                                @checked(old('is_active', $editingWallet->is_active ?? true))
                            >
                            Activo (permite movimientos)
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            {{ $editingWallet ? 'Guardar cambios' : 'Crear bolsillo' }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-4">
                @forelse ($wallets as $item)
                    @php
                        $wallet = $item['model'];
                        $balance = $item['balance'];
                    @endphp
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $wallet->name }}</h2>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $wallet->is_shared ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                                        {{ $wallet->is_shared ? 'Compartido' : 'Personal' }}
                                    </span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $wallet->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                                        {{ $wallet->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">
                                    @if($wallet->target_amount)
                                        Meta: {{ $fmtMoney($wallet->target_amount) }} ·
                                    @endif
                                    Creado {{ $wallet->created_at?->diffForHumans() ?? 'N/A' }}
                                </p>
                                @if($wallet->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $wallet->description }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">Saldo</p>
                                <p class="text-xl font-semibold text-emerald-600 dark:text-emerald-300">{{ $fmtMoney($balance) }}</p>
                                <div class="mt-2 flex items-center gap-3 justify-end text-xs">
                                    <a href="{{ route('wallets.index', ['edit' => $wallet->id]) }}" class="font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-300 dark:hover:text-amber-200">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('wallets.destroy', $wallet) }}" onsubmit="return confirm('¿Eliminar el bolsillo {{ $wallet->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-300 dark:hover:text-rose-200">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
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
                                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                        required
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha</label>
                                    <input
                                        type="date"
                                        name="occurred_on"
                                        value="{{ now()->format('Y-m-d') }}"
                                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                        required
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Concepto</label>
                                    <input
                                        type="text"
                                        name="concept"
                                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                        placeholder="Ajuste manual"
                                    >
                                </div>
                                <div class="md:col-span-3 flex justify-end">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                        @if(!$wallet->is_active) disabled @endif
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
                                            <p class="text-xs text-gray-500">{{ $movement->occurred_on?->format('d/m/Y') }}</p>
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
    </div>
</x-layouts.app>
