<x-layouts.app :title="__('Recurrencias')">
    @php
        $freqLabels = ['daily' => 'Diario', 'weekly' => 'Semanal', 'monthly' => 'Mensual', 'yearly' => 'Anual'];
    @endphp
    <div class="mx-auto w-full max-w-7xl space-y-6 px-3 sm:px-0">
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 shadow-lg sm:p-8 dark:from-emerald-600 dark:to-teal-700">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Automatiza</p>
                    <h1 class="text-3xl font-bold">Recurrencias</h1>
                    <p class="text-sm text-white/85">Pagos e ingresos programados; conéctalos al calendario.</p>
                </div>
                <div class="hero-chip text-sm font-semibold">Diario · Semanal · Mensual · Anual</div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="hero-chip text-xs">Sincroniza a Google Calendar</span>
                <span class="hero-chip text-xs">Controla estado activo</span>
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
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Nueva recurrencia</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Crea un gasto/ingreso repetitivo con próxima fecha y frecuencia.</p>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Tip: usa “Bolsillo” para separar saldos por objetivo.</p>
            </div>

            <form method="POST" action="{{ route('recurrences.store') }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @csrf
                <div>
                    <label for="rec-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                    <input
                        id="rec-name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        required
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rec-amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monto</label>
                    <input
                        id="rec-amount"
                        type="number"
                        step="0.01"
                        inputmode="decimal"
                        name="amount"
                        value="{{ old('amount') }}"
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        required
                    >
                    @error('amount')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rec-category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                    <select
                        id="rec-category"
                        name="category_id"
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none"
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
                    <label for="rec-wallet" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bolsillo</label>
                    <select
                        id="rec-wallet"
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

                <div>
                    <label for="rec-frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Frecuencia</label>
                    <select
                        id="rec-frequency"
                        name="frequency"
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none"
                        required
                    >
                        <option value="">Selecciona</option>
                        @foreach ($freqLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('frequency') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('frequency')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rec-next" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Próxima fecha</label>
                    <input
                        id="rec-next"
                        type="date"
                        name="next_run_on"
                        value="{{ old('next_run_on', now()->format('Y-m-d')) }}"
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        required
                    >
                    @error('next_run_on')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="rec-note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nota</label>
                    <input
                        id="rec-note"
                        type="text"
                        name="note"
                        value="{{ old('note') }}"
                        class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                    >
                    @error('note')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col justify-end space-y-2 md:col-span-2 lg:col-span-1">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input id="rec-active" type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800" checked>
                        Activa
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input id="rec-sync" type="checkbox" name="sync_to_calendar" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800">
                        Enviar a Google Calendar
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

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Listado</h2>
                    <p id="recurrences-count" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" role="status" aria-live="polite" aria-atomic="true">{{ $recurrences->count() }} recurrencias</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap md:justify-end">
                    <div class="min-w-0 sm:min-w-[260px]">
                        <label for="recurrences-search" class="sr-only">Buscar</label>
                        <input id="recurrences-search" type="search" autocomplete="off" placeholder="Buscar por nombre…" class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div class="min-w-0 sm:min-w-[180px]">
                        <label for="recurrences-frequency" class="sr-only">Frecuencia</label>
                        <select id="recurrences-frequency" class="h-12 w-full rounded-xl border border-gray-200 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none">
                            <option value="all">Frecuencia: Todas</option>
                            @foreach ($freqLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-0 sm:min-w-[160px]">
                        <label for="recurrences-status" class="sr-only">Estado</label>
                        <select id="recurrences-status" class="h-12 w-full rounded-xl border border-gray-200 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none">
                            <option value="all">Estado: Todos</option>
                            <option value="active">Activas</option>
                            <option value="inactive">Inactivas</option>
                        </select>
                    </div>
                    <button id="recurrences-clear" type="button" class="h-12 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800 dark:focus-visible:ring-offset-gray-900">
                        Limpiar
                    </button>
                </div>
            </div>

            <p id="recurrences-empty" class="hidden mt-4 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300" role="status" aria-live="polite">
                No hay recurrencias que coincidan con los filtros.
            </p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-[860px] w-full divide-y divide-gray-200 dark:divide-gray-800">
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
                                <td class="px-4 py-2 font-medium" data-rec-name="{{ strtolower($recurrence->name) }}">{{ $recurrence->name }}</td>
                                <td class="px-4 py-2">@money($recurrence->amount)</td>
                                <td class="px-4 py-2">{{ $recurrence->category?->name ?? 'Sin categoría' }}</td>
                                <td class="px-4 py-2" data-rec-frequency="{{ $recurrence->frequency }}">{{ $freqLabels[$recurrence->frequency] ?? ucfirst($recurrence->frequency) }}</td>
                                <td class="px-4 py-2">@dateCo($recurrence->next_run_on)</td>
                                <td class="px-4 py-2" data-rec-active="{{ $recurrence->is_active ? 'active' : 'inactive' }}">
                                    @if($recurrence->is_active)
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">Sí</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">No</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <form method="POST" action="{{ route('recurrences.destroy', $recurrence) }}" class="inline-flex items-center justify-end gap-2" data-inline-confirm>
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

    @push('scripts')
        <script>
            (() => {
                const boot = () => {
                    const searchEl = document.getElementById('recurrences-search');
                    const freqEl = document.getElementById('recurrences-frequency');
                    const statusEl = document.getElementById('recurrences-status');
                    const clearEl = document.getElementById('recurrences-clear');
                    const countEl = document.getElementById('recurrences-count');
                    const emptyEl = document.getElementById('recurrences-empty');

                    const rows = Array.from(document.querySelectorAll('tbody tr')).filter(tr => tr.querySelector('[data-rec-name]'));
                    if (rows.length === 0) return;

                    const apply = () => {
                        const q = (searchEl?.value || '').trim().toLowerCase();
                        const f = freqEl?.value || 'all';
                        const s = statusEl?.value || 'all';

                        let visible = 0;
                        rows.forEach((tr) => {
                            const name = tr.querySelector('[data-rec-name]')?.dataset?.recName || '';
                            const freq = tr.querySelector('[data-rec-frequency]')?.dataset?.recFrequency || '';
                            const active = tr.querySelector('[data-rec-active]')?.dataset?.recActive || '';

                            const okQ = !q || name.includes(q);
                            const okF = f === 'all' || String(freq) === String(f);
                            const okS = s === 'all' || String(active) === String(s);
                            const show = okQ && okF && okS;
                            tr.classList.toggle('hidden', !show);
                            if (show) visible++;
                        });

                        if (countEl) countEl.textContent = `${visible} recurrencias`;
                        if (emptyEl) emptyEl.classList.toggle('hidden', visible !== 0);
                    };

                    const clear = () => {
                        if (searchEl) searchEl.value = '';
                        if (freqEl) freqEl.value = 'all';
                        if (statusEl) statusEl.value = 'all';
                        apply();
                    };

                    searchEl?.addEventListener('input', apply);
                    freqEl?.addEventListener('change', apply);
                    statusEl?.addEventListener('change', apply);
                    clearEl?.addEventListener('click', clear);

                    apply();
                };

                document.addEventListener('DOMContentLoaded', boot);
                document.addEventListener('livewire:navigated', boot);
                document.addEventListener('turbo:load', boot);
                document.addEventListener('turbolinks:load', boot);
            })();
        </script>
    @endpush
</x-layouts.app>
