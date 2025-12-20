@php
    $fmtMoney = fn ($value) => '$' . number_format($value, 0, ',', '.');
@endphp

<x-layouts.app :title="__('Dashboard')">
    <div id="dashboard-content" class="mx-auto w-full max-w-6xl space-y-6">
        <a href="#dashboard-content" class="sr-only focus:not-sr-only focus:rounded-lg focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-semibold focus:text-gray-900 focus:ring-2 focus:ring-emerald-400 dark:focus:bg-neutral-900 dark:focus:text-gray-50">
            Saltar al contenido principal
        </a>

        {{-- Encabezado / contexto --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-50">
                    Dashboard
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Un hub para Finanzas, Alimentos y Familia.
                </p>
                @if($activeFamilyGroup)
                    <p class="mt-2 inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">
                        Núcleo activo: {{ $activeFamilyGroup->name }}
                    </p>
                @endif
            </div>

            <div class="flex flex-col items-start gap-2 md:items-end">
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-200 dark:ring-emerald-800/60">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Salud: {{ $healthScore }}/100
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button"
                            onclick="openNeedModal()"
                            aria-haspopup="dialog"
                            aria-controls="need-modal"
                            class="inline-flex items-center justify-center rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-emerald-900/60 dark:bg-emerald-900/30 dark:text-emerald-200 dark:hover:bg-emerald-900/40">
                        Agregar falta
                    </button>
                    <a href="{{ route('transactions.create') }}"
                       class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                        Registrar transacción
                    </a>
                    <a href="{{ route('food.shopping-list.index') }}"
                       class="inline-flex items-center justify-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-800 dark:bg-neutral-900 dark:text-gray-100 dark:hover:bg-neutral-800">
                        Lista de compras
                    </a>
                    <a href="{{ route('food.inventory.index') }}"
                       class="inline-flex items-center justify-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-800 dark:bg-neutral-900 dark:text-gray-100 dark:hover:bg-neutral-800">
                        Inventario
                    </a>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    @if($googleIntegration)
                        <form method="POST" action="{{ route('integrations.google.disconnect') }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="underline hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:hover:text-gray-200">
                                Google Calendar: conectado (desconectar)
                            </button>
                        </form>
                    @else
                        <a href="{{ route('integrations.google.redirect') }}" class="underline hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:hover:text-gray-200">
                            Google Calendar: conectar
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <section aria-labelledby="summary-title" class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="summary-title" class="text-lg font-semibold text-gray-900 dark:text-gray-50">
                        Resumen financiero
                    </h2>
                    <a href="{{ route('transactions.index') }}"
                       class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-emerald-300 dark:hover:text-emerald-200">
                        Ver detalle
                    </a>
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                        <dt class="text-xs font-medium text-gray-500">Ingresos (mes)</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $fmtMoney($income) }}</dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                        <dt class="text-xs font-medium text-gray-500">Gastos (mes)</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $fmtMoney($expenses) }}</dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                        <dt class="text-xs font-medium text-gray-500">Balance (mes)</dt>
                        <dd class="mt-1 text-lg font-semibold {{ $netThisMonth >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                            {{ $fmtMoney($netThisMonth) }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                        <dt class="text-xs font-medium text-gray-500">Presupuestos en riesgo</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $budgetsAtRiskCount }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="alerts-title" class="text-lg font-semibold text-gray-900 dark:text-gray-50">
                        Alertas
                    </h2>
                </div>
                <div class="mt-3 space-y-3">
                    @forelse ($alerts as $alert)
                        <div class="rounded-lg border border-amber-100 bg-amber-50 p-3 text-amber-900 dark:border-amber-900/50 dark:bg-amber-900/30 dark:text-amber-50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold">
                                        {{ $alert['title'] }}
                                    </p>
                                    <p class="mt-1 text-xs">
                                        {{ $alert['message'] }}
                                    </p>
                                </div>
                                @if(!empty($alert['route']))
                                    <a href="{{ $alert['route'] }}"
                                       class="shrink-0 rounded-lg bg-white/70 px-3 py-2 text-xs font-semibold text-amber-900 transition hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:bg-amber-950/40 dark:text-amber-50 dark:hover:bg-amber-950/60">
                                        Ver
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">
                            Sin alertas por ahora. ¡Buen trabajo!
                        </p>
                    @endforelse
                </div>
            </div>
        </section>

        <section aria-labelledby="recent-needs-title" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3">
                <div class="space-y-1">
                    <h2 id="recent-needs-title" class="text-lg font-semibold text-gray-900 dark:text-gray-50">
                        Ultimos agregados
                    </h2>
                    @if($foodLatestActiveList)
                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="rounded-full bg-gray-100 px-2 py-1 font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                {{ $foodLatestActiveList->name }}
                            </span>
                            @if($foodLatestActiveList->list_type)
                                <span class="rounded-full bg-emerald-50 px-2 py-1 font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
                                    Tipo: {{ $foodLatestActiveList->list_type }}
                                </span>
                            @endif
                            @if($foodLatestActiveList->expected_purchase_on)
                                <span class="rounded-full bg-gray-100 px-2 py-1 font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                    Compra: {{ $foodLatestActiveList->expected_purchase_on->format('d/m') }}
                                </span>
                            @endif
                            <span class="rounded-full bg-gray-100 px-2 py-1 font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                Pendientes: {{ $foodLatestActiveList->pending_items_count ?? 0 }}
                            </span>
                            <span class="rounded-full bg-gray-100 px-2 py-1 font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                Estimado: ${{ number_format($foodLatestActiveList->estimated_budget ?? 0, 0, ',', '.') }}
                            </span>
                            <span class="rounded-full bg-gray-100 px-2 py-1 font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                Real: ${{ number_format($foodLatestActiveList->actual_total ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif
                </div>
                @if($foodLatestActiveList)
                    <a href="{{ route('food.shopping-list.show', $foodLatestActiveList) }}"
                       class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-emerald-300 dark:hover:text-emerald-200">
                        Ver lista activa
                    </a>
                @endif
            </div>
            <div id="recent-needs-list" class="mt-3 space-y-2">
                @forelse ($recentNeedItems as $item)
                    <a href="{{ $foodLatestActiveList ? route('food.shopping-list.show', $foodLatestActiveList) : '#' }}"
                       class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-100 transition hover:bg-gray-100 dark:bg-neutral-900 dark:text-gray-200 dark:ring-gray-800 dark:hover:bg-neutral-800"
                       data-need-item
                       data-item-id="{{ $item->id }}"
                       data-product-id="{{ $item->product_id }}">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold" data-need-name>{{ $item->name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400" data-need-qty>x{{ (float) $item->qty_to_buy_base }}</span>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400" data-need-time>{{ $item->created_at?->diffForHumans() }}</span>
                    </a>
                @empty
                    <p id="recent-needs-empty" class="text-sm text-gray-500">Sin agregados recientes.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div id="need-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) closeNeedModal()" role="dialog" aria-modal="true" aria-labelledby="need-modal-title">
        <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl dark:bg-gray-900" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between">
                <h2 id="need-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-50">Agregar falta</h2>
                <button type="button" onclick="closeNeedModal()" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-gray-300 dark:hover:bg-gray-800">
                    <span class="sr-only">Cerrar</span>
                    ✕
                </button>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Escribe el nombre o escanea el código de barras.
            </p>

            <form id="need-form" method="POST" action="{{ route('food.needs.store') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label for="need-query" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre o codigo</label>
                    <div class="mt-1">
                        <input id="need-query"
                               name="query"
                               type="text"
                               required
                               maxlength="255"
                               value="{{ old('query') }}"
                               placeholder="Ej: Leche deslactosada"
                               class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-3 pr-9 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    @error('query')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div id="need-unit-wrapper" class="hidden">
                    <label for="need-unit-base" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unidad base (opcional)</label>
                    <select id="need-unit-base"
                            name="unit_base"
                            class="mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="unit" selected>Unidad</option>
                        <option value="g">Gramos</option>
                        <option value="kg">Kilogramos</option>
                        <option value="ml">Mililitros</option>
                        <option value="l">Litros</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Solo si el producto es nuevo.</p>
                    @error('confirm_new')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <input type="hidden" id="need-confirm-new" name="confirm_new" value="0">
                <p id="need-error" class="hidden text-xs text-rose-600"></p>
                <p id="need-loading" class="hidden text-xs text-emerald-700 dark:text-emerald-200">Guardando...</p>
                <div class="flex gap-2 pt-1">
                    <button type="button" onclick="closeNeedModal()" class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                        Cancelar
                    </button>
                    <button id="need-submit-btn" type="submit" class="flex-1 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                        Agregar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="need-toast" class="pointer-events-none fixed bottom-4 right-4 z-50 hidden">
        <div class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg">
            <span id="need-toast-message">Agregado.</span>
        </div>
    </div>

    @push('scripts')
    <script>
        const needModal = document.getElementById('need-modal');
        const needInput = document.getElementById('need-query');
        const needForm = document.getElementById('need-form');
        const needUnitWrapper = document.getElementById('need-unit-wrapper');
        const needConfirmNew = document.getElementById('need-confirm-new');
        const needError = document.getElementById('need-error');
        const needToast = document.getElementById('need-toast');
        const needToastMessage = document.getElementById('need-toast-message');
        const needLoading = document.getElementById('need-loading');
        const needSubmitBtn = document.getElementById('need-submit-btn');
        const recentNeedsList = document.getElementById('recent-needs-list');
        const activeListUrl = @json($foodLatestActiveList ? route('food.shopping-list.show', $foodLatestActiveList) : '');

        function openNeedModal() {
            if (!needModal) return;
            needModal.classList.remove('hidden');
            needModal.classList.add('flex');
            setTimeout(() => needInput?.focus(), 50);
        }

        function closeNeedModal() {
            if (!needModal) return;
            needModal.classList.add('hidden');
            needModal.classList.remove('flex');
            if (needUnitWrapper) needUnitWrapper.classList.add('hidden');
            if (needConfirmNew) needConfirmNew.value = '0';
            if (needError) {
                needError.textContent = '';
                needError.classList.add('hidden');
            }
            if (needLoading) needLoading.classList.add('hidden');
            if (needSubmitBtn) needSubmitBtn.disabled = false;
        }

        function showNeedError(message) {
            if (!needError) return;
            needError.textContent = message;
            needError.classList.remove('hidden');
        }

        function showNeedToast(message) {
            if (!needToast || !needToastMessage) return;
            needToastMessage.textContent = message;
            needToast.classList.remove('hidden');
            setTimeout(() => needToast.classList.add('hidden'), 2200);
        }

        function revealUnitBase() {
            if (needUnitWrapper) needUnitWrapper.classList.remove('hidden');
            if (needConfirmNew) needConfirmNew.value = '1';
        }

        async function submitNeedForm() {
            if (!needForm) return;
            if (needError) {
                needError.textContent = '';
                needError.classList.add('hidden');
            }
            if (needLoading) needLoading.classList.remove('hidden');
            if (needSubmitBtn) needSubmitBtn.disabled = true;

            const formData = new FormData(needForm);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

            try {
                const res = await fetch(needForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await res.json();

                if (res.ok && data?.status === 'ok') {
                    closeNeedModal();
                    showNeedToast(data.message || 'Agregado a la lista activa.');
                    if (needInput) needInput.value = '';
                    if (data.item) updateRecentNeeds(data.item, data.action || 'added');
                    return;
                }

                if (data?.status === 'needs_unit') {
                    revealUnitBase();
                    showNeedError(data.message || 'Confirma unidad base.');
                    return;
                }

                if (data?.errors?.query?.length) {
                    showNeedError(data.errors.query[0]);
                } else {
                    showNeedError(data?.message || 'No se pudo agregar.');
                }
            } catch (err) {
                console.error(err);
                showNeedError('Error al guardar. Intenta de nuevo.');
            } finally {
                if (needLoading) needLoading.classList.add('hidden');
                if (needSubmitBtn) needSubmitBtn.disabled = false;
            }
        }

        function updateRecentNeeds(item, action) {
            if (!recentNeedsList) return;

            const emptyState = document.getElementById('recent-needs-empty');
            if (emptyState) emptyState.remove();

            const existing = recentNeedsList.querySelector(`[data-product-id="${item.product_id}"]`);

            if (existing) {
                const qtyEl = existing.querySelector('[data-need-qty]');
                const timeEl = existing.querySelector('[data-need-time]');
                if (qtyEl) qtyEl.textContent = `x${item.qty_to_buy_base}`;
                if (timeEl) timeEl.textContent = item.created_at_human || 'ahora';
                recentNeedsList.prepend(existing);
                return;
            }

            const wrapper = document.createElement('a');
            wrapper.className = 'flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-100 transition hover:bg-gray-100 dark:bg-neutral-900 dark:text-gray-200 dark:ring-gray-800 dark:hover:bg-neutral-800';
            wrapper.setAttribute('data-need-item', '');
            wrapper.setAttribute('data-item-id', item.id);
            wrapper.setAttribute('data-product-id', item.product_id);
            wrapper.href = activeListUrl || '#';

            const left = document.createElement('div');
            left.className = 'flex items-center gap-2';

            const name = document.createElement('span');
            name.className = 'font-semibold';
            name.setAttribute('data-need-name', '');
            name.textContent = item.name;

            const qty = document.createElement('span');
            qty.className = 'text-xs text-gray-500 dark:text-gray-400';
            qty.setAttribute('data-need-qty', '');
            qty.textContent = `x${item.qty_to_buy_base}`;

            left.appendChild(name);
            left.appendChild(qty);

            const time = document.createElement('span');
            time.className = 'text-xs text-gray-500 dark:text-gray-400';
            time.setAttribute('data-need-time', '');
            time.textContent = item.created_at_human || 'ahora';

            wrapper.appendChild(left);
            wrapper.appendChild(time);

            recentNeedsList.prepend(wrapper);

            const items = recentNeedsList.querySelectorAll('[data-need-item]');
            if (items.length > 5) {
                items[items.length - 1].remove();
            }
        }

        if (needForm) {
            needForm.addEventListener('submit', (event) => {
                event.preventDefault();
                submitNeedForm();
            });
        }

        if (needInput && window.addScannerButton) {
            window.addScannerButton(needInput, {
                onScan: (code) => {
                    if (code) needInput.value = code;
                    if (needInput?.value?.trim()) {
                        submitNeedForm();
                    }
                }
            });
        } else if (needInput && window.BarcodeScanner) {
            const scanner = new window.BarcodeScanner({
                targetInput: needInput,
                onScan: (code) => {
                    if (code) needInput.value = code;
                    if (needInput?.value?.trim()) {
                        submitNeedForm();
                    }
                },
            });
            needInput.addEventListener('focus', () => scanner.open());
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && needModal && !needModal.classList.contains('hidden')) {
                closeNeedModal();
            }
        });

        @if($errors->has('query') || $errors->has('confirm_new') || $errors->has('unit_base'))
            openNeedModal();
            revealUnitBase();
        @endif
    </script>
    @endpush
</x-layouts.app>
