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
                @error('name')
                    <p class="text-sm text-rose-600 dark:text-rose-400 mb-3">{{ $message }}</p>
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
                            <th class="px-3 sm:px-6 py-3 text-right font-medium text-gray-900 dark:text-gray-100">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($list->items as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
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
                                <td class="px-3 sm:px-6 py-3 sm:py-4">
                                    <form method="POST"
                                        action="{{ route('food.shopping-list.items.destroy', [$list, $item]) }}"
                                        onsubmit="return confirm('¿Eliminar {{ $item->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="h-8 w-8 rounded-md text-gray-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-900/20"
                                            title="Eliminar">
                                            <svg class="h-5 w-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
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
        // Manejo de checkboxes para cambiar estado
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const itemId = this.dataset.itemId;
                const isChecked = this.checked;

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
                this.title = isChecked ? 'Marcar como pendiente' : 'Marcar como comprado';

                // Enviar actualización al servidor
                fetch(`/food/shopping-list/{{ $list->id }}/items/${itemId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ is_checked: isChecked ? 1 : 0 })
                }).catch(error => {
                    console.error('Error al actualizar estado:', error);
                    // Revertir UI en caso de error
                    this.checked = !isChecked;
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
        });

        // Manejo de cantidad editable
        document.querySelectorAll('.item-quantity').forEach(input => {
            let timeout;
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    const itemId = this.dataset.itemId;
                    const quantity = parseInt(this.value) || 0;

                    fetch(`/food/shopping-list/{{ $list->id }}/items/${itemId}/quantity`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ qty_to_buy_base: quantity })
                    }).catch(error => {
                        console.error('Error al actualizar cantidad:', error);
                    });
                }, 500); // Debounce de 500ms
            });
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
