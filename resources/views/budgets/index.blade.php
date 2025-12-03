<x-layouts.app :title="__('Presupuestos')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Planea antes de gastar</p>
                    <h1 class="text-3xl font-bold">Presupuestos</h1>
                    <p class="text-sm text-white/80">Define montos por categoría y mes, activa alertas y sincroniza con calendario.</p>
                </div>
                <div class="hero-chip text-sm font-semibold">
                    {{ now()->translatedFormat('F Y') }}
                </div>
            </div>
            <div class="hero-panel-content flex flex-wrap gap-2">
                <span class="hero-chip text-xs">Alertas al 80/90%</span>
                <span class="hero-chip text-xs">Sync opcional a Google Calendar</span>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
            <form method="POST" action="{{ route('budgets.store') }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                    <select
                        name="category_id"
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none"
                        required
                    >
                        <option value="">Selecciona</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
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
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        required
                    >
                    @error('amount')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mes</label>
                        <input
                            type="number"
                            name="month"
                            min="1"
                            max="12"
                            value="{{ old('month', $currentMonth) }}"
                            class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            required
                        >
                        @error('month')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Año</label>
                        <input
                            type="number"
                            name="year"
                            min="2000"
                            max="2100"
                            value="{{ old('year', $currentYear) }}"
                            class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            required
                        >
                        @error('year')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col justify-end space-y-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="is_flexible" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800">
                        Flexible (puede reasignar)
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="sync_to_calendar" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800">
                        Enviar a Google Calendar
                    </label>
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            Guardar presupuesto
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Listado</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-sm text-gray-600 dark:text-gray-400">
                            <th class="px-4 py-2">Categoría</th>
                            <th class="px-4 py-2">Monto</th>
                            <th class="px-4 py-2">Mes</th>
                            <th class="px-4 py-2">Año</th>
                            <th class="px-4 py-2">Flexible</th>
                            <th class="px-4 py-2">Calendario</th>
                            <th class="px-4 py-2 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($budgets as $budget)
                            <tr class="text-sm text-gray-800 dark:text-gray-100">
                                <td class="px-4 py-2 font-medium">{{ $budget->category?->name ?? 'Sin categoría' }}</td>
                                <td class="px-4 py-2">${{ number_format($budget->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-2">{{ $budget->month }}</td>
                                <td class="px-4 py-2">{{ $budget->year }}</td>
                                <td class="px-4 py-2">
                                    @if($budget->is_flexible)
                                        <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">Sí</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">No</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    @if($budget->sync_to_calendar)
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">Sí</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">No</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <form method="POST" action="{{ route('budgets.destroy', $budget) }}" onsubmit="return confirm('¿Eliminar presupuesto?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm text-rose-600 hover:underline dark:text-rose-300">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">Aún no hay presupuestos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
