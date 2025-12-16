<x-layouts.app :title="__('Presupuestos')">
    <div class="mx-auto w-full max-w-7xl space-y-6 px-3 sm:px-0">
        @php
            $monthOptions = collect(range(1, 12))->map(fn ($m) => [
                'value' => $m,
                'label' => \Carbon\Carbon::createFromDate($currentYear, $m, 1)->locale('es')->translatedFormat('F'),
            ]);
            $yearOptions = range($currentYear - 1, $currentYear + 2);
            $monthLabels = $monthOptions->pluck('label', 'value');

            $budgetsJs = $budgets->map(function ($b) {
                return [
                    'category_id' => (int) $b->category_id,
                    'month' => (int) $b->month,
                    'year' => (int) $b->year,
                    'amount' => (float) $b->amount,
                    'is_flexible' => (bool) $b->is_flexible,
                    'sync_to_calendar' => (bool) $b->sync_to_calendar,
                ];
            })->values();
        @endphp
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 shadow-lg sm:p-8 dark:from-emerald-600 dark:to-teal-700">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Planea antes de gastar</p>
                    <h1 class="text-3xl font-bold">Presupuestos</h1>
                    <p class="text-sm text-white/80">Define montos por categoría y mes, activa alertas y sincroniza con calendario.</p>
                </div>
                <div class="hero-chip text-sm font-semibold">
                    {{ now()->locale('es')->translatedFormat('F Y') }}
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
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
            <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Crear / actualizar</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Si ya existe un presupuesto para esa categoría y mes, se actualizará.</p>
                </div>
                <div id="budget-form-hint" role="status" aria-live="polite" aria-atomic="true" class="hidden rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-100 dark:ring-emerald-900/60"></div>
            </div>

            <form id="budget-form" method="POST" action="{{ route('budgets.store') }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @csrf
                <div>
                    <label for="budget-category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                    <select
                        id="budget-category"
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
                    <label for="budget-amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monto</label>
                    <input
                        id="budget-amount"
                        type="number"
                        step="0.01"
                        name="amount"
                        value="{{ old('amount') }}"
                        placeholder="Ej: 350000"
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        required
                    >
                    @error('amount')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="budget-month" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mes</label>
                        <select
                            id="budget-month"
                            name="month"
                            class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none"
                            required
                        >
                            @foreach ($monthOptions as $opt)
                                <option value="{{ $opt['value'] }}" @selected((int) old('month', $currentMonth) === (int) $opt['value'])>
                                    {{ ucfirst($opt['label']) }}
                                </option>
                            @endforeach
                        </select>
                        @error('month')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="budget-year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Año</label>
                        <select
                            id="budget-year"
                            name="year"
                            class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none"
                            required
                        >
                            @foreach ($yearOptions as $y)
                                <option value="{{ $y }}" @selected((int) old('year', $currentYear) === (int) $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                        @error('year')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="lg:col-span-4 flex flex-col gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div class="flex flex-wrap gap-6">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input id="budget-flexible" type="checkbox" name="is_flexible" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800">
                                Flexible (puede reasignar)
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input id="budget-sync" type="checkbox" name="sync_to_calendar" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800">
                                Enviar a Google Calendar
                            </label>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center md:justify-end">
                            <button type="button" id="budget-clear" class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800 dark:focus-visible:ring-offset-gray-900 sm:w-auto">
                                Limpiar
                            </button>
                            <button
                                id="budget-submit"
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto"
                            >
                                Guardar
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tip: selecciona categoría + mes/año para cargar un presupuesto existente (si existe) y editarlo.</p>
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Listado</h2>
                    <p id="budgets-count" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" role="status" aria-live="polite" aria-atomic="true">{{ $budgets->count() }} presupuestos</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap md:justify-end">
                    <div class="min-w-0 sm:min-w-[260px]">
                        <label for="budgets-search" class="sr-only">Buscar</label>
                        <input id="budgets-search" type="search" autocomplete="off" placeholder="Buscar por categoría…" class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div class="min-w-0 sm:min-w-[140px]">
                        <label for="budgets-month" class="sr-only">Mes</label>
                        <select id="budgets-month" class="h-12 w-full rounded-xl border border-gray-200 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none">
                            <option value="all">Mes: Todos</option>
                            @foreach ($monthOptions as $opt)
                                <option value="{{ $opt['value'] }}">{{ ucfirst($opt['label']) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-0 sm:min-w-[120px]">
                        <label for="budgets-year" class="sr-only">Año</label>
                        <select id="budgets-year" class="h-12 w-full rounded-xl border border-gray-200 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none">
                            <option value="all">Año: Todos</option>
                            @foreach ($yearOptions as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="budgets-clear" type="button" class="h-12 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800 dark:focus-visible:ring-offset-gray-900">
                        Limpiar
                    </button>
                </div>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-[760px] w-full divide-y divide-gray-200 dark:divide-gray-800">
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
                                <td class="px-4 py-2 font-medium" data-budget-category="{{ strtolower($budget->category?->name ?? 'sin categoría') }}">{{ $budget->category?->name ?? 'Sin categoría' }}</td>
                                <td class="px-4 py-2">${{ number_format($budget->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-2" data-budget-month="{{ (int) $budget->month }}">{{ ucfirst($monthLabels[(int) $budget->month] ?? (string) $budget->month) }}</td>
                                <td class="px-4 py-2" data-budget-year="{{ (int) $budget->year }}">{{ $budget->year }}</td>
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
                                    <div class="inline-flex items-center justify-end gap-3">
                                        <button
                                            type="button"
                                            class="text-sm font-semibold text-emerald-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 dark:text-emerald-300 dark:focus-visible:ring-offset-gray-900"
                                            data-budget-edit
                                            data-budget-category-id="{{ (int) $budget->category_id }}"
                                            data-budget-month="{{ (int) $budget->month }}"
                                            data-budget-year="{{ (int) $budget->year }}"
                                            title="Cargar este presupuesto en el formulario"
                                        >
                                            Editar
                                        </button>
                                        <form method="POST" action="{{ route('budgets.destroy', $budget) }}" class="inline-flex items-center justify-end gap-2" data-inline-confirm>
                                            @csrf
                                            @method('DELETE')
                                            <span class="sr-only" role="status" aria-live="polite" data-inline-confirm-status></span>
                                            <button type="button" class="text-sm font-semibold text-rose-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 focus-visible:ring-offset-2 dark:text-rose-300 dark:focus-visible:ring-offset-gray-900" data-inline-confirm-arm>
                                                Eliminar
                                            </button>
                                            <button type="submit" class="hidden text-sm font-semibold text-rose-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 focus-visible:ring-offset-2 dark:text-rose-200 dark:focus-visible:ring-offset-gray-900" data-inline-confirm-confirm>
                                                Confirmar
                                            </button>
                                            <button type="button" class="hidden text-sm font-semibold text-gray-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2 dark:text-gray-300 dark:focus-visible:ring-offset-gray-900" data-inline-confirm-cancel>
                                                Cancelar
                                            </button>
                                        </form>
                                    </div>
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const budgets = @json($budgetsJs);

                const categoryEl = document.getElementById('budget-category');
                const monthEl = document.getElementById('budget-month');
                const yearEl = document.getElementById('budget-year');
                const amountEl = document.getElementById('budget-amount');
                const flexibleEl = document.getElementById('budget-flexible');
                const syncEl = document.getElementById('budget-sync');
                const hintEl = document.getElementById('budget-form-hint');
                const submitEl = document.getElementById('budget-submit');
                const clearEl = document.getElementById('budget-clear');

                const index = new Map(budgets.map(b => [`${b.category_id}:${b.month}:${b.year}`, b]));

                const showHint = (text) => {
                    if (!hintEl) return;
                    hintEl.textContent = text || '';
                    hintEl.classList.toggle('hidden', !text);
                };

                const keyOf = () => `${categoryEl?.value || ''}:${monthEl?.value || ''}:${yearEl?.value || ''}`;

                const updateFromSelection = () => {
                    const b = index.get(keyOf());
                    if (b) {
                        if (amountEl) amountEl.value = String(b.amount ?? '');
                        if (flexibleEl) flexibleEl.checked = !!b.is_flexible;
                        if (syncEl) syncEl.checked = !!b.sync_to_calendar;
                        if (submitEl) submitEl.textContent = 'Actualizar';
                        showHint('Este presupuesto ya existe: se actualizará.');
                    } else {
                        if (submitEl) submitEl.textContent = 'Guardar';
                        showHint('');
                    }
                };

                const clearForm = () => {
                    if (categoryEl) categoryEl.value = '';
                    if (amountEl) amountEl.value = '';
                    if (flexibleEl) flexibleEl.checked = false;
                    if (syncEl) syncEl.checked = false;
                    if (submitEl) submitEl.textContent = 'Guardar';
                    showHint('');
                };

                categoryEl?.addEventListener('change', updateFromSelection);
                monthEl?.addEventListener('change', updateFromSelection);
                yearEl?.addEventListener('change', updateFromSelection);
                clearEl?.addEventListener('click', clearForm);

                updateFromSelection();

                // List filters (client-side)
                const searchListEl = document.getElementById('budgets-search');
                const monthListEl = document.getElementById('budgets-month');
                const yearListEl = document.getElementById('budgets-year');
                const clearListEl = document.getElementById('budgets-clear');
                const countEl = document.getElementById('budgets-count');

                const rows = Array.from(document.querySelectorAll('tbody tr')).filter(tr => tr.querySelector('[data-budget-category]'));

                const applyListFilters = () => {
                    const q = (searchListEl?.value || '').trim().toLowerCase();
                    const m = monthListEl?.value || 'all';
                    const y = yearListEl?.value || 'all';

                    let visible = 0;
                    rows.forEach((tr) => {
                        const category = tr.querySelector('[data-budget-category]')?.dataset?.budgetCategory || '';
                        const month = tr.querySelector('[data-budget-month]')?.dataset?.budgetMonth || '';
                        const year = tr.querySelector('[data-budget-year]')?.dataset?.budgetYear || '';

                        const okQ = !q || category.includes(q);
                        const okM = m === 'all' || String(month) === String(m);
                        const okY = y === 'all' || String(year) === String(y);
                        const show = okQ && okM && okY;
                        tr.classList.toggle('hidden', !show);
                        if (show) visible++;
                    });

                    if (countEl) countEl.textContent = `${visible} presupuestos`;
                };

                const clearListFilters = () => {
                    if (searchListEl) searchListEl.value = '';
                    if (monthListEl) monthListEl.value = 'all';
                    if (yearListEl) yearListEl.value = 'all';
                    applyListFilters();
                };

                searchListEl?.addEventListener('input', applyListFilters);
                monthListEl?.addEventListener('change', applyListFilters);
                yearListEl?.addEventListener('change', applyListFilters);
                clearListEl?.addEventListener('click', clearListFilters);

                applyListFilters();

                // Quick edit: load row into the form
                document.querySelectorAll('[data-budget-edit]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const cid = btn.getAttribute('data-budget-category-id') || '';
                        const m = btn.getAttribute('data-budget-month') || '';
                        const y = btn.getAttribute('data-budget-year') || '';

                        if (categoryEl) categoryEl.value = cid;
                        if (monthEl) monthEl.value = m;
                        if (yearEl) yearEl.value = y;
                        categoryEl?.dispatchEvent(new Event('change', { bubbles: true }));
                        monthEl?.dispatchEvent(new Event('change', { bubbles: true }));
                        yearEl?.dispatchEvent(new Event('change', { bubbles: true }));

                        document.getElementById('budget-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        amountEl?.focus?.();
                    });
                });
            });
        </script>
    @endpush
</x-layouts.app>
