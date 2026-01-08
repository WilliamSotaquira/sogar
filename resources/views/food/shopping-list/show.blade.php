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
                    <div class="flex items-center gap-2">
                        <a href="{{ route('food.shopping-list.all') }}"
                           class="{{ $btnSecondary }}">
                            Ver todas las listas
                        </a>
                        <a href="{{ route('food.shopping-list.exportCsv', $list) }}"
                           class="{{ $btnSecondary }}">
                            Exportar (CSV)
                        </a>
                        <button type="button"
                            onclick="openDeleteListModal()"
                            class="{{ $btnSecondary }} text-rose-600 border-rose-200 hover:bg-rose-50 dark:border-rose-900/40 dark:text-rose-300 dark:hover:bg-rose-900/30">
                            Eliminar lista
                        </button>
                        <button type="submit" class="{{ $btnPrimary }}">Guardar cambios</button>
                    </div>
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
                    <div class="flex-1">
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

            <div class="border-b border-gray-200 px-4 py-3 sm:px-6 dark:border-gray-800">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <span>Acciones multiples</span>
                        <span id="bulk-checked-count" class="text-xs text-gray-500 dark:text-gray-400">0 seleccionados</span>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-gray-600 dark:text-gray-300">
                            <input type="checkbox" id="bulk-select-all" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            Seleccionar todos
                        </label>
                        <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                            <label class="sr-only" for="bulk-action-select">Accion</label>
                            <select id="bulk-action-select"
                                class="col-span-2 h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 sm:col-auto sm:w-44 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                <option value="">Accion...</option>
                                <option value="mark">Marcar comprados</option>
                                <option value="unmark">Marcar pendientes</option>
                                <option value="delete">Eliminar seleccionados</option>
                            </select>
                            <button type="button" id="bulk-apply-btn"
                                class="inline-flex h-9 w-full items-center justify-center rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                                Aplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left font-medium text-gray-900 dark:text-gray-100">Sel</th>
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
                                        class="bulk-item-checkbox h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                        data-item-id="{{ $item->id }}">
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4">
                                    <div>
                                        @if($item->product)
                                            <a href="{{ route('food.products.show', $item->product) }}"
                                               class="font-medium text-gray-900 underline decoration-gray-300 underline-offset-2 hover:text-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-gray-100 dark:hover:text-emerald-200">
                                                {{ $item->name }}
                                            </a>
                                        @else
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item->name }}</div>
                                        @endif
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
                                    <button type="button"
                                        class="item-toggle"
                                        data-item-id="{{ $item->id }}"
                                        data-checked="{{ $item->is_checked ? '1' : '0' }}">
                                        <span class="item-status-badge-{{ $item->id }} inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $item->is_checked ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                            {{ $item->is_checked ? 'Comprado' : 'Pendiente' }}
                                        </span>
                                    </button>
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

    <div id="delete-list-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) closeDeleteListModal()" role="dialog" aria-modal="true" aria-labelledby="delete-list-title">
        <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl dark:bg-gray-900" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between">
                <h2 id="delete-list-title" class="text-lg font-semibold text-gray-900 dark:text-gray-50">Eliminar lista</h2>
                <button type="button" onclick="closeDeleteListModal()" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-gray-300 dark:hover:bg-gray-800">
                    <span class="sr-only">Cerrar</span>
                    ✕
                </button>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Esta accion elimina la lista y todos sus productos. Escribe el nombre exacto para confirmar.
            </p>
            <form method="POST" action="{{ route('food.shopping-list.destroy', $list) }}" class="mt-4 space-y-3">
                @csrf
                @method('DELETE')
                <div>
                    <label for="delete-list-confirm" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la lista</label>
                    <input id="delete-list-confirm"
                        type="text"
                        placeholder="{{ $list->name }}"
                        class="mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" onclick="closeDeleteListModal()" class="{{ $btnSecondary }} flex-1">Cancelar</button>
                    <button id="delete-list-submit" type="submit" class="{{ $btnPrimary }} flex-1 bg-rose-600 hover:bg-rose-700 focus:ring-rose-500">
                        Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const bulkCheckedCount = document.getElementById('bulk-checked-count');
        const bulkSelectAll = document.getElementById('bulk-select-all');
        const bulkActionSelect = document.getElementById('bulk-action-select');
        const bulkApplyBtn = document.getElementById('bulk-apply-btn');
        const deleteListModal = document.getElementById('delete-list-modal');
        const deleteListConfirm = document.getElementById('delete-list-confirm');
        const deleteListSubmit = document.getElementById('delete-list-submit');
        const listName = @json($list->name ?? '');

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
                        class="bulk-item-checkbox h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                        data-item-id="${item.id}">
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
                    <button type="button"
                        class="item-toggle"
                        data-item-id="${item.id}"
                        data-checked="0">
                        <span class="item-status-badge-${item.id} inline-flex rounded-full px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            Pendiente
                        </span>
                    </button>
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
            updateBulkState();
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
                updateBulkState();
            } catch (err) {
                console.error(err);
                setStatus('Error de red al eliminar.', true);
            }
        });

        // Toggle de estado (comprado / pendiente)
        document.addEventListener('click', (event) => {
            const target = event.target instanceof Element ? event.target.closest('.item-toggle') : null;
            if (!target) return;

            const itemId = target.getAttribute('data-item-id');
            if (!itemId) return;

            const isChecked = target.getAttribute('data-checked') === '1';
            const nextChecked = !isChecked;

            const statusBadge = document.querySelector(`.item-status-badge-${itemId}`);
            if (statusBadge) {
                if (nextChecked) {
                    statusBadge.className = 'item-status-badge-' + itemId + ' inline-flex rounded-full px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
                    statusBadge.textContent = 'Comprado';
                } else {
                    statusBadge.className = 'item-status-badge-' + itemId + ' inline-flex rounded-full px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
                    statusBadge.textContent = 'Pendiente';
                }
            }

            target.setAttribute('data-checked', nextChecked ? '1' : '0');

            fetch(`/food/shopping-list/{{ $list->id }}/items/${itemId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ is_checked: nextChecked ? 1 : 0 })
            }).catch(error => {
                console.error('Error al actualizar estado:', error);
                target.setAttribute('data-checked', isChecked ? '1' : '0');
                if (statusBadge) {
                    if (isChecked) {
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

        const updateBulkState = () => {
            const checked = document.querySelectorAll('.bulk-item-checkbox:checked');
            const count = checked.length;
            if (bulkCheckedCount) bulkCheckedCount.textContent = `${count} seleccionados`;
            if (bulkApplyBtn) {
                const action = bulkActionSelect?.value || '';
                bulkApplyBtn.disabled = count === 0 || action === '';
            }
            if (bulkSelectAll) {
                const total = document.querySelectorAll('.bulk-item-checkbox').length;
                bulkSelectAll.checked = total > 0 && count === total;
                bulkSelectAll.indeterminate = count > 0 && count < total;
            }
        };

        bulkSelectAll?.addEventListener('change', (event) => {
            const checked = event.target.checked;
            document.querySelectorAll('.bulk-item-checkbox').forEach((box) => {
                box.checked = checked;
            });
            updateBulkState();
        });

        document.addEventListener('change', (event) => {
            const target = event.target;
            if (target instanceof HTMLInputElement && target.classList.contains('bulk-item-checkbox')) {
                updateBulkState();
            }
        });

        bulkActionSelect?.addEventListener('change', () => {
            updateBulkState();
        });

        bulkApplyBtn?.addEventListener('click', async () => {
            const action = bulkActionSelect?.value || '';
            if (!action) return;

            const selectedIds = Array.from(document.querySelectorAll('.bulk-item-checkbox:checked'))
                .map((box) => box.getAttribute('data-item-id'))
                .filter(Boolean);

            if (selectedIds.length === 0) return;

            if (action === 'delete') {
                const confirmDelete = confirm(`Eliminar ${selectedIds.length} productos?`);
                if (!confirmDelete) return;
            }

            try {
                const res = await fetch(`/food/shopping-list/{{ $list->id }}/items/bulk`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        items: selectedIds.map((id) => Number(id)),
                        action,
                    }),
                });

                if (!res.ok) {
                    setStatus('No se pudo ejecutar la accion.', true);
                    return;
                }

                if (action === 'delete') {
                    selectedIds.forEach((id) => {
                        document.querySelector(`tr[data-item-row-id="${id}"]`)?.remove();
                    });
                    setStatus('Productos eliminados.');
                } else if (action === 'mark' || action === 'unmark') {
                    const markValue = action === 'mark';
                    selectedIds.forEach((id) => {
                        const toggleBtn = document.querySelector(`.item-toggle[data-item-id="${id}"]`);
                        if (toggleBtn) toggleBtn.setAttribute('data-checked', markValue ? '1' : '0');
                        const statusBadge = document.querySelector(`.item-status-badge-${id}`);
                        if (statusBadge) {
                            if (markValue) {
                                statusBadge.className = 'item-status-badge-' + id + ' inline-flex rounded-full px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
                                statusBadge.textContent = 'Comprado';
                            } else {
                                statusBadge.className = 'item-status-badge-' + id + ' inline-flex rounded-full px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
                                statusBadge.textContent = 'Pendiente';
                            }
                        }
                    });
                    setStatus(markValue ? 'Productos marcados como comprados.' : 'Productos desmarcados.');
                }

                if (bulkActionSelect) bulkActionSelect.value = '';
                document.querySelectorAll('.bulk-item-checkbox:checked').forEach((box) => {
                    box.checked = false;
                });
                updateBulkState();
            } catch (err) {
                console.error(err);
                setStatus('Error de red al ejecutar la accion.', true);
            }
        });

        window.openDeleteListModal = () => {
            if (!deleteListModal || !deleteListConfirm || !deleteListSubmit) return;
            deleteListModal.classList.remove('hidden');
            deleteListModal.classList.add('flex');
            deleteListSubmit.disabled = true;
            deleteListConfirm.value = '';
            setTimeout(() => deleteListConfirm.focus(), 50);
        };

        window.closeDeleteListModal = () => {
            if (!deleteListModal) return;
            deleteListModal.classList.add('hidden');
            deleteListModal.classList.remove('flex');
        };

        deleteListConfirm?.addEventListener('input', () => {
            if (!deleteListSubmit) return;
            deleteListSubmit.disabled = deleteListConfirm.value.trim() !== listName;
        });

        // Barcode Scanner usando componente reutilizable
        const productInput = document.getElementById('product-name-input');

        if (productInput && window.addScannerButton) {
            window.addScannerButton(productInput, {
                onScan: (code) => {
                    console.log('Producto escaneado:', code);
                }
            });
        }

        updateBulkState();
    });
</script>
