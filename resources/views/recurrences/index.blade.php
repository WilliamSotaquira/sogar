<x-layouts.app :title="__('Recurrencias')">
    <div class="mx-auto w-full max-w-5xl space-y-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-50">Recurrencias</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">Configura pagos/ingresos automáticos.</p>
            </div>
            <div class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                Ejecución básica: daily/weekly/monthly/yearly
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form method="POST" action="{{ route('recurrences.store') }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        required
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monto</label>
                    <input
                        type="number"
                        step="0.01"
                        name="amount"
                        value="{{ old('amount') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        required
                    >
                    @error('amount')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                    <select
                        name="category_id"
                        class="mt-1 w-full rounded-lg border-gray-300 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        required
                    >
                        <option value="">Selecciona</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                {{ ucfirst($category->type === 'income' ? 'Ingreso' : 'Gasto') }} · {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bolsillo</label>
                    <select
                        name="wallet_id"
                        class="mt-1 w-full rounded-lg border-gray-300 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Frecuencia</label>
                    <select
                        name="frequency"
                        class="mt-1 w-full rounded-lg border-gray-300 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        required
                    >
                        <option value="">Selecciona</option>
                        @foreach (['daily' => 'Diario', 'weekly' => 'Semanal', 'monthly' => 'Mensual', 'yearly' => 'Anual'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('frequency') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('frequency')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Próxima fecha</label>
                    <input
                        type="date"
                        name="next_run_on"
                        value="{{ old('next_run_on', now()->format('Y-m-d')) }}"
                        class="mt-1 w-full rounded-lg border-gray-300 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        required
                    >
                    @error('next_run_on')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nota</label>
                    <input
                        type="text"
                        name="note"
                        value="{{ old('note') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                    >
                    @error('note')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col justify-end space-y-2 md:col-span-2 lg:col-span-1">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800" checked>
                        Activa
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="sync_to_calendar" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800">
                        Enviar a calendario (futuro)
                    </label>
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            Guardar recurrencia
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Listado</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-sm text-gray-600 dark:text-gray-400">
                            <th class="px-4 py-2">Nombre</th>
                            <th class="px-4 py-2">Monto</th>
                            <th class="px-4 py-2">Categoría</th>
                            <th class="px-4 py-2">Frecuencia</th>
                            <th class="px-4 py-2">Próxima</th>
                            <th class="px-4 py-2">Activa</th>
                            <th class="px-4 py-2 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($recurrences as $recurrence)
                            <tr class="text-sm text-gray-800 dark:text-gray-100">
                                <td class="px-4 py-2 font-medium">{{ $recurrence->name }}</td>
                                <td class="px-4 py-2">${{ number_format($recurrence->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-2">{{ $recurrence->category?->name ?? 'Sin categoría' }}</td>
                                <td class="px-4 py-2 capitalize">{{ $recurrence->frequency }}</td>
                                <td class="px-4 py-2">{{ optional($recurrence->next_run_on)->format('Y-m-d') }}</td>
                                <td class="px-4 py-2">
                                    @if($recurrence->is_active)
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">Sí</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">No</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <form method="POST" action="{{ route('recurrences.destroy', $recurrence) }}" onsubmit="return confirm('¿Eliminar recurrencia?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm text-rose-600 hover:underline dark:text-rose-300">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">Aún no hay recurrencias.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
