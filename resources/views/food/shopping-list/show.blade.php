@php
    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1';
    $input = 'h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
    $btnPrimary = 'inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 touch-target';
    $btnSecondary = 'inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700 touch-target';
@endphp

<x-layouts.app :title="__('Lista de compra') . ' ' . ($list->name ?? '')">
    <div class="mx-auto w-full max-w-6xl space-y-4 px-3 sm:px-4 md:px-6">

        {{-- Header --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form method="POST" action="{{ route('food.shopping-list.update', $list) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label for="list-name" class="{{ $label }}">Nombre de la lista</label>
                        <input type="text" id="list-name" name="name" value="{{ $list->name }}"
                            class="{{ $input }}" required>
                    </div>
                    <div>
                        <label for="expected-date" class="{{ $label }}">Fecha estimada</label>
                        <input type="date" id="expected-date" name="expected_purchase_on"
                            value="{{ $list->expected_purchase_on?->format('Y-m-d') }}"
                            class="{{ $input }}">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <span>Generada: {{ $list->generated_at?->format('d/m/Y H:i') }}</span>
                        <span>{{ $list->people_count }} personas</span>
                        <span>{{ $list->purchase_frequency_days }} días</span>
                        <span class="capitalize">Estado: {{ $list->status }}</span>
                    </div>
                    <button type="submit" class="{{ $btnPrimary }}">Guardar cambios</button>
                </div>
            </form>
        </div>

        {{-- Productos --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 p-4 sm:p-6 dark:border-gray-800">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-50 mb-4">Productos</h2>

                {{-- Agregar producto --}}
                <form method="POST" action="{{ route('food.shopping-list.items.store') }}"
                    class="flex flex-col sm:flex-row gap-3 mb-4" id="add-product-form">
                    @csrf
                    <input type="hidden" name="list_id" value="{{ $list->id }}">
                    <input type="hidden" name="create_product" value="0" id="create-product-flag">
                    <div class="relative flex-1">
                        <label for="product-name-input" class="sr-only">Producto</label>
                        <input type="text" id="product-name-input" name="name" list="products-list"
                            placeholder="Selecciona un producto del catálogo"
                            class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-3 pr-9 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" required autocomplete="off">
                        <datalist id="products-list">
                            @foreach($products as $product)
                                <option value="{{ $product->name }}" data-id="{{ $product->id }}">
                                    {{ $product->name }}{{ $product->barcode ? ' (' . $product->barcode . ')' : '' }}
                                </option>
                            @endforeach
                        </datalist>
                        <button type="button" id="barcode-scanner-btn"
                            class="absolute right-0 top-0 h-10 w-10 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                            title="Escanear código de barras">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </button>
                    </div>
                    <div>
                        <label for="qty-input" class="sr-only">Cantidad</label>
                        <input type="number" id="qty-input" name="qty_to_buy_base" step="1" min="1" value="1"
                            class="h-10 w-20 rounded-lg border border-gray-200 bg-white px-3 text-sm text-center dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <button type="submit" class="{{ $btnPrimary }}">Adicionar</button>
                </form>
                <div class="flex items-center justify-between gap-3">
                    <button type="button"
                        id="toggle-quick-create"
                        class="text-sm font-semibold text-emerald-700 underline decoration-emerald-200 underline-offset-2 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-emerald-300 dark:hover:text-emerald-200"
                        aria-expanded="false"
                        aria-controls="quick-create-panel">
                        Crear producto nuevo (rápido)
                    </button>
                    <p id="add-item-status" role="status" aria-live="polite" aria-atomic="true" class="text-sm text-gray-500 dark:text-gray-400"></p>
                </div>
                <div id="quick-create-panel" class="hidden mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="{{ $label }}">Marca (opcional)</label>
                            <input type="text" name="brand" form="add-product-form" class="{{ $input }}" placeholder="Ej: Alpina">
                        </div>
                        <div>
                            <label class="{{ $label }}">Tipo (opcional)</label>
                            <select name="type_id" form="add-product-form" class="{{ $input }}">
                                <option value="">Sin tipo</option>
                                @foreach(($foodTypes ?? collect()) as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $label }}">Unidad base *</label>
                            <select name="unit_base" form="add-product-form" class="{{ $input }}">
                                <option value="unit">Unidad</option>
                                <option value="g">g</option>
                                <option value="kg">kg</option>
                                <option value="ml">ml</option>
                                <option value="l">l</option>
                            </select>
                        </div>
                        <div>
                            <label class="{{ $label }}">Código de barras (opcional)</label>
                            <input type="text" name="barcode" form="add-product-form" class="{{ $input }}" placeholder="Ej: 770..." inputmode="numeric">
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Escribe el nombre arriba y presiona “Adicionar”. Si no existe, se creará y se añadirá a la lista.
                    </p>
                </div>

                @error('name')
                    <p class="text-sm text-rose-600 dark:text-rose-400 mt-3">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tabla --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left font-medium text-gray-900 dark:text-gray-100">✓</th>
                            <th class="px-3 sm:px-6 py-3 text-left font-medium text-gray-900 dark:text-gray-100">Producto</th>
                            <th class="px-3 sm:px-6 py-3 text-left font-medium text-gray-900 dark:text-gray-100">Cantidad</th>
                            <th class="px-3 sm:px-6 py-3 text-left font-medium text-gray-900 dark:text-gray-100">Stock</th>
                            <th class="px-3 sm:px-6 py-3 text-left font-medium text-gray-900 dark:text-gray-100">Prioridad</th>
                            <th class="px-3 sm:px-6 py-3 text-left font-medium text-gray-900 dark:text-gray-100">Estado</th>
                            <th class="px-3 sm:px-6 py-3 text-right font-medium text-gray-900 dark:text-gray-100 w-20 whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($list->items as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50" data-item-row-id="{{ $item->id }}">
                                <td class="px-3 sm:px-6 py-3 sm:py-4">
                                    <input type="checkbox"
                                        data-item-id="{{ $item->id }}"
                                        {{ $item->is_checked ? 'checked' : '' }}
                                        class="item-checkbox h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                                        title="Marcar como {{ $item->is_checked ? 'pendiente' : 'comprado' }}">
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item->name }}</div>
                                        @if($item->product)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                Código: {{ $item->product->barcode ?: 'Sin código' }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4">
                                    <input type="number"
                                        data-item-id="{{ $item->id }}"
                                        value="{{ (int) $item->qty_to_buy_base }}"
                                        min="0"
                                        step="1"
                                        class="item-quantity h-8 w-16 rounded-md border border-gray-300 bg-white px-2 text-xs text-center dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 text-gray-600 dark:text-gray-400">
                                    {{ number_format((float) ($item->qty_current_base ?? 0), 0, ',', '.') }} {{ $item->unit_base }}
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
                                        {{ $item->priority === 'high' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' : '' }}
                                        {{ $item->priority === 'medium' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : '' }}
                                        {{ $item->priority === 'low' ? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' : '' }}">
                                        {{ ucfirst($item->priority) }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4">
                                    <span class="item-status-badge-{{ $item->id }} inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $item->is_checked ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                        {{ $item->is_checked ? 'Comprado' : 'Pendiente' }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 text-right w-20 whitespace-nowrap">
                                    <button type="button"
                                        class="item-delete h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:bg-rose-50 hover:text-rose-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-rose-900/20"
                                        data-item-id="{{ $item->id }}"
                                        data-armed="0"
                                        title="Eliminar">
                                        <span class="sr-only">Eliminar</span>
                                        <svg class="h-5 w-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <p class="mb-2">No hay productos en la lista</p>
                                    <p class="text-sm text-gray-400">Agrega productos usando el formulario superior</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de escaneo de código de barras (se crea dinámicamente por BarcodeScanner) -->
</x-layouts.app>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Toggle quick create panel
        const toggleQuickCreate = document.getElementById('toggle-quick-create');
        const quickCreatePanel = document.getElementById('quick-create-panel');
        const createFlag = document.getElementById('create-product-flag');
        const statusEl = document.getElementById('add-item-status');

        const setStatus = (text, isError = false) => {
            if (!statusEl) return;
            statusEl.textContent = text || '';
            statusEl.className = isError
                ? 'text-sm text-rose-600 dark:text-rose-300'
                : 'text-sm text-gray-500 dark:text-gray-400';
        };

        const setQuickCreateOpen = (open) => {
            if (!toggleQuickCreate || !quickCreatePanel || !createFlag) return;
            toggleQuickCreate.setAttribute('aria-expanded', open ? 'true' : 'false');
            quickCreatePanel.classList.toggle('hidden', !open);
            createFlag.value = open ? '1' : '0';
        };

        toggleQuickCreate?.addEventListener('click', () => {
            const isOpen = toggleQuickCreate.getAttribute('aria-expanded') === 'true';
            setQuickCreateOpen(!isOpen);
        });

        // AJAX add (and optionally create product)
        const addForm = document.getElementById('add-product-form');
        const nameInput = document.getElementById('product-name-input');
        const qtyInput = document.getElementById('qty-input');
        const productsDatalist = document.getElementById('products-list');
        const tbody = document.querySelector('tbody.divide-y');

        const escapeHtml = (s) => String(s ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const priorityBadgeClass = (priority) => {
            if (priority === 'high') return 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300';
            if (priority === 'medium') return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
        };

        const addRow = (item) => {
            if (!tbody) return;
            const emptyRow = tbody.querySelector('tr td[colspan="7"]')?.closest('tr');
            emptyRow?.remove();

            const barcode = item.product?.barcode || '';
            const stock = Number(item.qty_current_base || 0);
            const unitBase = item.unit_base || 'unit';
            const priority = item.priority || 'medium';

            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50 dark:hover:bg-gray-800/50';
            row.dataset.itemRowId = String(item.id);
            row.innerHTML = `
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                    <input type="checkbox"
                        data-item-id="${item.id}"
                        class="item-checkbox h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                        title="Marcar como comprado">
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">${escapeHtml(item.name)}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Código: ${barcode ? escapeHtml(barcode) : 'Sin código'}
                        </div>
                    </div>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                    <input type="number"
                        data-item-id="${item.id}"
                        value="${Number(item.qty_to_buy_base || 1)}"
                        min="0"
                        step="1"
                        class="item-quantity h-8 w-16 rounded-md border border-gray-300 bg-white px-2 text-xs text-center dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-gray-600 dark:text-gray-400">
                    ${stock.toLocaleString('es-CO')} ${escapeHtml(unitBase)}
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium ${priorityBadgeClass(priority)}">
                        ${escapeHtml(priority.charAt(0).toUpperCase() + priority.slice(1))}
                    </span>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                    <span class="item-status-badge-${item.id} inline-flex rounded-full px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        Pendiente
                    </span>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                    <button type="button"
                        class="item-delete h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:bg-rose-50 hover:text-rose-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-rose-900/20"
                        data-item-id="${item.id}"
                        data-armed="0"
                        title="Eliminar">
                        <span class="sr-only">Eliminar</span>
                        <svg class="h-5 w-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </td>
            `;

            tbody.prepend(row);
        };

        addForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            setStatus('');

            const fd = new FormData(addForm);
            try {
                setStatus('Agregando…');
                const res = await fetch(addForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: fd,
                });

                const payload = await res.json().catch(() => null);
                if (!res.ok) {
                    const msg = payload?.message || payload?.errors?.name?.[0] || 'No se pudo agregar.';
                    setStatus(msg, true);
                    if (msg.includes('Crear nuevo')) {
                        setQuickCreateOpen(true);
                    }
                    return;
                }

                if (payload?.item) {
                    addRow(payload.item);
                }

                if (payload?.item?.product && productsDatalist) {
                    const opt = document.createElement('option');
                    const b = payload.item.product.barcode ? ` (${payload.item.product.barcode})` : '';
                    opt.value = payload.item.product.name;
                    opt.textContent = `${payload.item.product.name}${b}`;
                    productsDatalist.appendChild(opt);
                }

                if (nameInput) nameInput.value = '';
                if (qtyInput) qtyInput.value = '1';
                setStatus(payload?.product_created ? 'Producto creado y agregado.' : 'Producto agregado.');
            } catch (err) {
                console.error(err);
                setStatus('Error de red al agregar.', true);
            }
        });

        // Eliminar ítem (2 clics, sin confirm del navegador)
        const deleteTimers = {};
        document.addEventListener('click', async (event) => {
            const target = event.target instanceof Element ? event.target.closest('.item-delete') : null;
            if (!target) return;

            const itemId = target.getAttribute('data-item-id');
            if (!itemId) return;

            const armed = target.getAttribute('data-armed') === '1';
            if (!armed) {
                target.setAttribute('data-armed', '1');
                target.classList.add('bg-rose-50', 'text-rose-600');
                target.classList.remove('text-gray-400', 'text-gray-500');
                target.title = 'Click de nuevo para eliminar';

                clearTimeout(deleteTimers[itemId]);
                deleteTimers[itemId] = setTimeout(() => {
                    target.setAttribute('data-armed', '0');
                    target.classList.remove('bg-rose-50', 'text-rose-600');
                    target.classList.add('text-gray-500');
                    target.title = 'Eliminar';
                }, 4000);

                return;
            }

            clearTimeout(deleteTimers[itemId]);

            try {
                const res = await fetch(`/food/shopping-list/{{ $list->id }}/items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                const payload = await res.json().catch(() => null);
                if (!res.ok) {
                    const msg = payload?.message || 'No se pudo eliminar.';
                    setStatus(msg, true);
                    target.setAttribute('data-armed', '0');
                    target.classList.remove('bg-rose-50', 'text-rose-600');
                    target.classList.add('text-gray-500');
                    target.title = 'Eliminar';
                    return;
                }

                const row = document.querySelector(`tr[data-item-row-id="${itemId}"]`);
                row?.remove();
                setStatus('Producto eliminado.');
            } catch (err) {
                console.error(err);
                setStatus('Error de red al eliminar.', true);
            }
        });

        // Manejo de checkboxes para cambiar estado
        document.addEventListener('change', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLInputElement) || !target.classList.contains('item-checkbox')) {
                return;
            }

            const itemId = target.dataset.itemId;
            const isChecked = target.checked;

                // Actualización optimista de UI
                const statusBadge = document.querySelector(`.item-status-badge-${itemId}`);
                if (statusBadge) {
                    if (isChecked) {
                        statusBadge.className = 'item-status-badge-' + itemId + ' inline-flex rounded-full px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
                        statusBadge.textContent = 'Comprado';
                    } else {
                        statusBadge.className = 'item-status-badge-' + itemId + ' inline-flex rounded-full px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
                        statusBadge.textContent = 'Pendiente';
                    }
                }

                // Actualizar título del checkbox
                target.title = isChecked ? 'Marcar como pendiente' : 'Marcar como comprado';

                // Enviar actualización al servidor
                fetch(`/food/shopping-list/{{ $list->id }}/items/${itemId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ is_checked: isChecked ? 1 : 0 })
                }).catch(error => {
                    console.error('Error al actualizar estado:', error);
                    // Revertir UI en caso de error
                    target.checked = !isChecked;
                    if (statusBadge) {
                        if (!isChecked) {
                            statusBadge.className = 'item-status-badge-' + itemId + ' inline-flex rounded-full px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
                            statusBadge.textContent = 'Comprado';
                        } else {
                            statusBadge.className = 'item-status-badge-' + itemId + ' inline-flex rounded-full px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
                            statusBadge.textContent = 'Pendiente';
                        }
                    }
                });
        });

        // Manejo de cantidad editable
        const qtyTimers = {};
        document.addEventListener('input', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLInputElement) || !target.classList.contains('item-quantity')) {
                return;
            }

            const itemId = target.dataset.itemId;
            clearTimeout(qtyTimers[itemId]);
            qtyTimers[itemId] = setTimeout(() => {
                const quantity = parseInt(target.value) || 0;

                    fetch(`/food/shopping-list/{{ $list->id }}/items/${itemId}/quantity`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ qty_to_buy_base: quantity })
                    }).catch(error => {
                        console.error('Error al actualizar cantidad:', error);
                    });
            }, 500); // Debounce de 500ms
        });

        // Barcode Scanner usando componente reutilizable
        const productInput = document.getElementById('product-name-input');
        const scannerBtn = document.getElementById('barcode-scanner-btn');

        if (productInput && scannerBtn && window.BarcodeScanner) {
            const scanner = new window.BarcodeScanner({
                targetInput: productInput,
                onScan: (code) => {
                    console.log('Producto escaneado:', code);
                    // El código ya se insertó en el input automáticamente
                }
            });

            scannerBtn.addEventListener('click', () => scanner.open());
        }
    });
</script>
