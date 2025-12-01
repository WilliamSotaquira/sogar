<x-layouts.app :title="__('Registrar transacción')">
    <div class="mx-auto w-full max-w-3xl space-y-6">
        <div class="rounded-2xl bg-gradient-to-r from-emerald-500 via-green-500 to-lime-400 p-6 text-white shadow-lg">
            <p class="text-sm uppercase tracking-wide font-semibold">Entrada rápida</p>
            <h1 class="text-3xl font-bold">Registrar transacción</h1>
            <p class="text-sm text-white/85">Sugerencias por palabra clave, categoría y bolsillo en un solo paso.</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('transactions.store') }}" class="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monto</label>
                    <input
                        type="number"
                        step="0.01"
                        name="amount"
                        id="amount"
                        value="{{ old('amount') }}"
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        required
                    >
                    @error('amount')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="occurred_on" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha</label>
                    <input
                        type="date"
                        name="occurred_on"
                        id="occurred_on"
                        value="{{ old('occurred_on', now()->format('Y-m-d')) }}"
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        required
                    >
                    @error('occurred_on')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Categoría
                        @if($suggestedCategoryId)
                            <span class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">Sugerida</span>
                        @endif
                    </label>
                    <select
                        id="category_id"
                        name="category_id"
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none"
                    >
                        <option value="">Selecciona</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                @selected(old('category_id', $suggestedCategoryId) == $category->id)>
                                {{ ucfirst($category->type === 'income' ? 'Ingreso' : 'Gasto') }} · {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="wallet_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bolsillo</label>
                    <select
                        id="wallet_id"
                        name="wallet_id"
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none"
                    >
                        <option value="">Sin bolsillo</option>
                        @foreach ($wallets as $wallet)
                            <option value="{{ $wallet->id }}" @selected(old('wallet_id') == $wallet->id)>{{ $wallet->name }}</option>
                        @endforeach
                    </select>
                    @error('wallet_id')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nota / palabra clave</label>
                <input
                    type="text"
                    name="note"
                    id="note"
                    value="{{ old('note') }}"
                    placeholder="Ej: super compras quincena"
                    class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                >
                @error('note')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Usa palabras clave como "super", "colegio", "arriendo" para sugerir categoría.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 underline dark:text-gray-300">Cancelar</a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                >
                    Guardar
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
