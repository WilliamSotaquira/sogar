<x-layouts.app :title="__('Lista de compra') . ' ' . ($list->name ?? '')">
    <div class="mx-auto w-full max-w-5xl space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Detalle de
                        lista</p>
                    <form method="POST" action="{{ route('food.shopping-list.update', $list) }}"
                        class="flex flex-wrap items-center gap-2">
                        @csrf
                        @method('PUT')
                        <input type="text" name="name" value="{{ $list->name }}"
                            class="h-10 rounded-lg border border-gray-200 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            aria-label="Nombre de la lista">
                        <input type="date" name="expected_purchase_on"
                            value="{{ $list->expected_purchase_on?->format('Y-m-d') }}"
                            class="h-10 rounded-lg border border-gray-200 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            aria-label="Fecha estimada de compra">
                        <button
                            class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Guardar</button>
                    </form>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Generada {{ $list->generated_at?->format('Y-m-d H:i:s') }} · Estado: {{ $list->status }}
                    </p>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Personas: {{ $list->people_count }} · Horizonte: {{ $list->purchase_frequency_days }} días
                </div>
            </div>
        </div>

        <div
            class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-3">
            <div class="flex flex-col gap-3">
                <div class="flex flex-wrap items-center gap-2 justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Productos</h2>
                    <form method="POST" action="{{ route('food.shopping-list.items.store') }}"
                        class="flex flex-wrap items-center gap-2 text-sm">
                        @csrf
                        <input type="hidden" name="list_id" value="{{ $list->id }}">
                        <input type="text" name="name" placeholder="Producto"
                            class="h-10 rounded-lg border border-gray-200 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            required>
                        <input type="number" name="qty_to_buy_base" step="1" min="1" value="1"
                            class="h-10 w-20 rounded-lg border border-gray-200 bg-white px-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            required>
                        <button type="submit"
                            class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Adicionar</button>
                    </form>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-800/50">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">Acciones masivas</p>
                    <form id="bulk-form" method="POST" action="{{ route('food.shopping-list.items.bulk', $list) }}"
                        class="flex flex-wrap items-center gap-2 text-sm justify-end">
                        @csrf
                        <input type="hidden" id="bulk-action" name="action" value="mark">
                        <button type="submit"
                            class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 whitespace-nowrap"
                            onclick="document.getElementById('bulk-action').value='mark'">Comprado</button>
                        <button type="submit"
                            class="rounded-lg bg-amber-500 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-600 whitespace-nowrap"
                            onclick="document.getElementById('bulk-action').value='unmark'">Pendiente</button>
                        <button type="submit"
                            class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700 whitespace-nowrap"
                            onclick="document.getElementById('bulk-action').value='delete'; return confirm('¿Eliminar seleccionados?');">Eliminar</button>
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-100 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr class="text-left text-xs uppercase text-gray-500">
                            <th class="px-3 py-2">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300">
                            </th>
                            <th class="px-3 py-2">Nombre</th>
                            <th class="px-3 py-2">Cantidad</th>
                            <th class="px-3 py-2">Stock actual</th>
                            <th class="px-3 py-2">Prioridad</th>
                            <th class="px-3 py-2">Estado</th>
                            <th class="px-3 py-2">Acción</th>
                            <th class="px-3 py-2">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list->items as $item)
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="px-3 py-2">
                                    <input type="checkbox" name="items[]" value="{{ $item->id }}" form="bulk-form"
                                        class="row-checkbox rounded border-gray-300">
                                </td>
                                <td class="px-3 py-2 font-semibold text-gray-900 dark:text-gray-50">{{ $item->name }}
                                </td>
                                <td class="px-3 py-2">{{ number_format((float) $item->qty_to_buy_base, 0, ',', '.') }}
                                    {{ $item->unit_base }}</td>
                                <td class="px-3 py-2">
                                    {{ number_format((float) ($item->qty_current_base ?? 0), 0, ',', '.') }}
                                    {{ $item->unit_base }}</td>
                                <td class="px-3 py-2">{{ ucfirst($item->priority) }}</td>
                                <td class="px-3 py-2">
                                    @if ($item->is_checked)
                                        <span
                                            class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-100">Comprado</span>
                                    @else
                                        <span
                                            class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">Pendiente</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 min-w-[280px]">
                                    <form method="POST"
                                        action="{{ route('food.shopping-list.items.mark', [$list, $item->id]) }}"
                                        class="flex flex-wrap items-center gap-2">
                                        @csrf
                                        <input type="number" name="qty_to_buy_base" step="1" min="0"
                                            value="{{ (int) $item->qty_to_buy_base }}"
                                            class="h-9 w-16 rounded-lg border border-gray-200 bg-white px-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                            aria-label="Cantidad a comprar">
                                        <select name="is_checked"
                                            class="h-9 min-w-[120px] rounded-lg border border-gray-200 bg-white px-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                            <option value="0" @selected(!$item->is_checked)>Pendiente</option>
                                            <option value="1" @selected($item->is_checked)>Comprado</option>
                                        </select>

                                    </form>
                                </td>
                                <td class="px-3 py-2">
                                    <form method="POST"
                                        action="{{ route('food.shopping-list.items.destroy', [$list, $item]) }}"
                                        onsubmit="return confirm('¿Eliminar este producto de la lista?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-xs font-semibold text-rose-600 hover:text-rose-700">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-4 text-center text-gray-500">Sin items en la lista.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.row-checkbox');
        if (selectAll && checkboxes.length) {
            selectAll.addEventListener('change', () => {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            });
        }
    });
</script>
