<x-layouts.app :title="__('Compras de Alimentos')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
    @endphp
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Gasto de alimentos</p>
                    <h1 class="text-3xl font-bold">Compras</h1>
                    <p class="text-sm text-white/80">Registra tickets y vincula presupuesto, categoría y wallet.</p>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Registrar compra</h2>
            <form method="POST" action="{{ route('food.purchases.store') }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="{{ $label }}">Fecha</label>
                        <input type="date" name="occurred_on" value="{{ now()->format('Y-m-d') }}" class="{{ $input }}" required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Proveedor</label>
                        <input name="vendor" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Wallet</label>
                        <select name="wallet_id" class="{{ $input }}">
                            <option value="">Sin wallet</option>
                            @foreach($wallets as $wallet)
                                <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="impact_finanzas" value="1" class="rounded">
                            Impactar finanzas
                        </label>
                    </div>
                </div>

                <div class="border rounded-xl border-dashed p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold">
                            Items @if($activeList)<span class="text-xs text-gray-500">(desde lista: {{ $activeList->name }})</span>@endif
                        </div>
                        <button type="button" id="add-item" class="{{ $btnSecondary }} text-xs">Agregar</button>
                    </div>
                    <div id="items-container" class="space-y-3">
                        <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4 border rounded-lg p-3 item-row">
                            <input type="hidden" name="items[0][unit_size]" value="1">
                            <div>
                                <label class="block text-xs text-gray-600">Producto (lista)</label>
                                <select name="items[0][product_id]" class="{{ $input }} item-product-select">
                                    <option value="">Nuevo / libre</option>
                                    @foreach($listItems as $li)
                                        <option value="{{ $li->product_id }}" data-name="{{ $li->name }}" data-qty="{{ $li->qty_to_buy_base }}" data-unit="{{ $li->unit_base }}" data-unitsize="{{ $li->unit_size }}" data-location="{{ $li->location_id }}" data-type="{{ $li->product?->type_id }}">
                                            {{ $li->name }} @if($li->product?->brand) ({{ $li->product->brand }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <input name="items[0][name]" placeholder="Nombre rápido" class="{{ $input }} mt-1">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">Tipo</label>
                                <select name="items[0][type_id]" class="{{ $input }}">
                                    <option value="">—</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">Ubicación</label>
                                <select name="items[0][location_id]" class="{{ $input }}">
                                    <option value="">—</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-600">Qty</label>
                                    <input name="items[0][qty]" value="1" class="{{ $input }} item-qty">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Unidad</label>
                                    <input name="items[0][unit]" value="unit" class="{{ $input }} item-unit">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Precio</label>
                                    <input name="items[0][unit_price]" value="0" class="{{ $input }}">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-600">Categoría</label>
                                    <select name="items[0][category_id]" class="{{ $input }}">
                                        <option value="">—</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600">Presupuesto</label>
                                    <select name="items[0][budget_id]" class="{{ $input }}">
                                        <option value="">—</option>
                                        @foreach($budgets as $budget)
                                            <option value="{{ $budget->id }}">{{ $budget->category?->name }} ({{ $budget->month }}/{{ $budget->year }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">Caducidad</label>
                                <input type="date" name="items[0][expires_on]" class="{{ $input }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="{{ $btnPrimary }}">Guardar compra</button>
                </div>
            </form>
        </div>

    </div>

    <script>
        const container = document.getElementById('items-container');
        const applyListPreset = (row) => {
            const select = row.querySelector('.item-product-select');
            const qty = row.querySelector('.item-qty');
            const unit = row.querySelector('.item-unit');
            if (!select || !qty || !unit) return;
            select.addEventListener('change', () => {
                const opt = select.selectedOptions[0];
                if (!opt) return;
                const presetQty = opt.dataset.qty;
                const presetUnit = opt.dataset.unit;
                const presetUnitSize = opt.dataset.unitsize;
                const presetLocation = opt.dataset.location;
                const presetType = opt.dataset.type;
                const nameInput = row.querySelector('input[name*="[name]"]');
                const locSelect = row.querySelector('select[name*="[location_id]"]');
                const typeSelect = row.querySelector('select[name*="[type_id]"]');
                const unitSizeInput = row.querySelector('input[name*="[unit_size]"]');
                if (presetQty) qty.value = presetQty;
                if (presetUnit) unit.value = presetUnit;
                if (presetUnitSize && unitSizeInput) unitSizeInput.value = presetUnitSize;
                if (presetLocation && locSelect) locSelect.value = presetLocation;
                if (presetType && typeSelect) typeSelect.value = presetType;
                if (nameInput && opt.dataset.name) nameInput.value = opt.dataset.name;
            });
        };
        applyListPreset(container.firstElementChild);

        document.getElementById('add-item').addEventListener('click', function () {
            const idx = container.querySelectorAll('.item-row').length;
            const tpl = container.firstElementChild.cloneNode(true);
            tpl.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/\d+/, idx);
                if (el.type === 'text' || el.tagName === 'SELECT' || el.type === 'number' || el.type === 'date') {
                    if (!el.name.includes('unit')) el.value = '';
                    if (el.name.includes('[qty]')) el.value = '1';
                    if (el.name.includes('[unit_price]')) el.value = '0';
                }
                if (el.classList.contains('item-product-select')) {
                    el.value = '';
                }
            });
            container.appendChild(tpl);
            applyListPreset(tpl);
        });
    </script>
</x-layouts.app>
