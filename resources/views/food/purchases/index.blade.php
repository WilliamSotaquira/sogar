<x-layouts.app :title="__('Compras de Alimentos')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
        $totalListItems = $selectedList?->items->count() ?? 0;
        $pendingListItems = $selectedList?->items->filter(fn ($item) => empty($item->is_checked))->count() ?? 0;
        $budgetLabel = $selectedList?->budget?->category?->name
            ? $selectedList->budget->category->name . ' · ' . str_pad($selectedList->budget->month, 2, '0', STR_PAD_LEFT) . '/' . $selectedList->budget->year
            : 'Sin presupuesto asignado';
        $pendingInventoryCount = $pendingInventoryCount ?? 0;
        $pendingInventoryPreview = ($pendingInventoryItems ?? collect())->take(3);
    @endphp
    <div class="mx-auto w-full max-w-6xl space-y-6 pb-28 md:pb-0">
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-8 shadow-lg dark:from-emerald-600 dark:to-teal-700">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Gasto de alimentos</p>
                    <h1 class="text-3xl font-bold">Compras</h1>
                    <p class="text-sm text-white/80">Registra tickets y vincula presupuesto, categoría y wallet.</p>
                    @if($pendingInventoryCount > 0)
                        <span class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white">
                            ⚠️ Pendiente inventario: {{ $pendingInventoryCount }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if (session('status'))
            <div role="status" aria-live="polite" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if($pendingInventoryCount > 0)
            <div class="rounded-lg border border-amber-200 bg-amber-50/70 p-5 shadow-sm dark:border-amber-900/40 dark:bg-amber-900/20">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-200">Alertas de sincronización</p>
                        <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-100">{{ $pendingInventoryCount }} ítem{{ $pendingInventoryCount === 1 ? '' : 's' }} marcados sin lote en inventario</h3>
                        <p class="text-sm text-amber-700/80 dark:text-amber-100/80">Confirma estos productos para evitar diferencias entre compras e inventario.</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach($pendingInventoryPreview as $pendingItem)
                        <div class="flex flex-col gap-2 rounded-xl border border-amber-100 bg-white/70 px-4 py-3 text-sm text-amber-900 shadow-sm dark:border-amber-900/40 dark:bg-amber-900/30 dark:text-amber-100 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold">{{ $pendingItem->name }}</p>
                                <p class="text-xs text-amber-700/80 dark:text-amber-100/80">Marcado {{ optional($pendingItem->checked_at)->diffForHumans() ?? 'sin fecha' }} · Cantidad {{ $pendingItem->qty_to_buy_base }} {{ $pendingItem->unit_base }}</p>
                            </div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-200">Pendiente</p>
                        </div>
                    @endforeach
                </div>

                @if($pendingInventoryCount > $pendingInventoryPreview->count())
                    <p class="mt-3 text-xs text-amber-700/80 dark:text-amber-100/80">+{{ $pendingInventoryCount - $pendingInventoryPreview->count() }} ítem{{ ($pendingInventoryCount - $pendingInventoryPreview->count()) === 1 ? '' : 's' }} adicionales pendientes.</p>
                @endif
            </div>
        @endif

        @if($lists->isEmpty())
            <div class="rounded-lg border border-dashed border-gray-300 bg-white/80 p-10 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
                <div class="mb-4 text-4xl">🛒</div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Necesitas una lista activa para registrar compras</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Genera o abre una lista de compras para poder validar qué artículos fueron adquiridos.</p>
                <a href="{{ route('food.shopping-list.index') }}" class="mt-6 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Ir a mis listas</a>
            </div>
        @else
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900" role="region" aria-labelledby="selected-list-title">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p id="selected-list-title" class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Lista seleccionada</p>
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $selectedList->name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Generada {{ optional($selectedList->generated_at)->format('d M Y') ?? '—' }} · Estado: <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ strtoupper($selectedList->status ?? 'activo') }}</span>
                        </p>
                    </div>
                    <dl class="grid gap-3 text-sm md:grid-cols-3">
                        <div class="rounded-xl bg-gray-50/80 px-4 py-3 dark:bg-gray-800/40">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Items totales</dt>
                            <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalListItems }}</dd>
                        </div>
                        <div class="rounded-xl bg-amber-50/80 px-4 py-3 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200">
                            <dt class="text-xs uppercase tracking-wide">Pendientes</dt>
                            <dd class="text-xl font-semibold">{{ $pendingListItems }}</dd>
                        </div>
                        <div class="rounded-xl bg-gray-50/80 px-4 py-3 dark:bg-gray-800/40">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Presupuesto</dt>
                            <dd class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $budgetLabel }}</dd>
                        </div>
                    </dl>
                </div>
                <form method="GET" action="{{ route('food.purchases.index') }}" class="mt-4">
                    <label for="list_id" class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Cambiar lista</label>
                    <select id="list_id" name="list_id" class="{{ $input }} mt-1" onchange="this.form.submit()">
                        @foreach($lists as $list)
                            <option value="{{ $list->id }}" @selected($selectedList && $list->id === $selectedList->id)>
                                {{ $list->name }} · {{ $list->items->count() }} ítems
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <form id="purchase-form" method="POST" action="{{ route('food.purchases.store') }}" class="space-y-6" aria-labelledby="purchase-form-title">
                @csrf
                <input type="hidden" name="shopping_list_id" value="{{ $selectedList->id }}">
                <span id="purchase-form-title" class="sr-only">Registrar compra a partir de la lista seleccionada</span>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900" role="region" aria-labelledby="purchase-details-title">
                        <h3 id="purchase-details-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Detalles de la compra</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Define cuándo y dónde hiciste la compra.</p>
                        <div class="mt-4 space-y-3">
                            <div>
                                <label class="{{ $label }}">Fecha de compra</label>
                                <input type="date" name="occurred_on" value="{{ now()->timezone('America/Bogota')->format('Y-m-d') }}" class="{{ $input }}" required>
                            </div>
                            <div>
                                <label class="{{ $label }}">Lugar / Proveedor</label>
                                <input name="vendor" placeholder="Ej: Supermercado Central" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">Nota</label>
                                <textarea name="note" rows="2" class="{{ $input }} resize-none"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900" role="region" aria-labelledby="payment-title">
                        <h3 id="payment-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pago y control</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Asocia método, transacción y opciones financieras.</p>
                        <div class="mt-4 space-y-3">
                            <div>
                                <label class="{{ $label }}">Método de pago / Wallet</label>
                                <select name="wallet_id" class="{{ $input }}">
                                    <option value="">Sin wallet</option>
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="{{ $label }}">Número de transacción</label>
                                <input name="receipt_number" class="{{ $input }}" placeholder="Factura, ticket o referencia">
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-gray-50/80 px-4 py-3 dark:bg-gray-800/40">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Impactar finanzas</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Genera movimiento contable al guardar.</p>
                                </div>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                    <input type="checkbox" name="impact_finanzas" value="1" class="h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    Sí
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-[3fr_2fr]">
                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900" role="region" aria-labelledby="confirm-items-title" aria-describedby="confirm-items-desc">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 id="confirm-items-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Confirmar ítems</h3>
                                <p id="confirm-items-desc" class="text-sm text-gray-500 dark:text-gray-400">Marca qué productos realmente compraste y ajusta cantidades o precios.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <span class="rounded-full bg-gray-100 px-3 py-1 dark:bg-gray-800/50">{{ $pendingListItems }} ítems pendientes</span>
                                <div class="inline-flex gap-2 text-[11px] font-semibold normal-case">
                                    <button type="button" id="items-select-all" class="rounded-full border border-gray-200 px-3 py-1 text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-100">Seleccionar todo</button>
                                    <button type="button" id="items-clear-all" class="rounded-full border border-gray-200 px-3 py-1 text-gray-700 hover:border-rose-500 hover:text-rose-600 dark:border-gray-700 dark:text-gray-100">Limpiar selección</button>
                                </div>
                                @if($pendingInventoryCount > 0)
                                    <div class="inline-flex overflow-hidden rounded-full border border-gray-200 text-[11px] font-semibold normal-case dark:border-gray-700" role="group" aria-label="Filtrar ítems por estado de inventario">
                                        <button type="button" data-items-filter="all" aria-pressed="true" class="pending-filter-btn inline-flex items-center gap-1 px-3 py-1 text-gray-700 transition hover:text-emerald-600 dark:text-gray-100">
                                            Todos
                                        </button>
                                        <button type="button" data-items-filter="pending" aria-pressed="false" class="pending-filter-btn inline-flex items-center gap-1 border-l border-gray-200 px-3 py-1 text-gray-700 transition hover:text-emerald-600 dark:border-gray-700 dark:text-gray-100">
                                            Pendientes inv.
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-900/50 dark:text-amber-100">{{ $pendingInventoryCount }}</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($listItems->isEmpty())
                            <div class="mt-4 rounded-xl border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                La lista seleccionada no tiene productos para confirmar.
                            </div>
                        @else
                            <div id="purchase-items" class="mt-4 overflow-x-auto rounded-lg border border-gray-100 bg-white/80 p-4 shadow-sm dark:border-gray-800/60 dark:bg-gray-900/40">
                                <table class="min-w-full text-sm">
                                    <thead class="border-b border-gray-100 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-800/60 dark:text-gray-300">
                                        <tr>
                                            <th scope="col" class="py-3 pl-4 pr-2 text-gray-500 dark:text-gray-300">Ítem</th>
                                            <th scope="col" class="py-3 px-2 text-gray-500 dark:text-gray-300">Cantidad</th>
                                            <th scope="col" class="py-3 px-2 text-gray-500 dark:text-gray-300">Unidad</th>
                                            <th scope="col" class="py-3 px-2 text-gray-500 dark:text-gray-300">Precio unit.</th>
                                            <th scope="col" class="py-3 pr-4 pl-2 text-right text-gray-500 dark:text-gray-300">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-900/40">
                                        @foreach($listItems as $index => $item)
                                            @php
                                                $qtyDefault = $item->qty_to_buy_base ?? 1;
                                                $unitDefault = $item->unit_base ?? 'unit';
                                                $priceDefault = $item->estimated_price ?? 0;
                                                $locationId = $item->location_id ?? $item->product?->default_location_id;
                                                $locationLabel = $item->location->name
                                                    ?? $item->product?->defaultLocation?->name
                                                    ?? 'Sin ubicación asignada';
                                                $initialSubtotal = (int) round($qtyDefault) * (int) round($priceDefault);
                                                $inventoryBatchId = data_get($item->metadata, 'inventory_batch_id');
                                                $isPendingInventory = $item->is_checked && empty($inventoryBatchId);
                                            @endphp
                                            <tr class="item-row transition hover:bg-emerald-50/40 dark:hover:bg-emerald-900/20" data-row-index="{{ $index }}" data-pending-inventory="{{ $isPendingInventory ? '1' : '0' }}" role="row">
                                                <td class="py-4 pl-4 pr-2 align-top">
                                                    <label class="flex items-start gap-2 text-gray-700 dark:text-gray-200">
                                                        <input type="checkbox" name="items[{{ $index }}][include]" value="1" class="item-include mt-1 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" checked aria-describedby="item-meta-{{ $index }}">
                                                        <div>
                                                            <p id="item-title-{{ $index }}" class="font-semibold text-gray-900 dark:text-gray-100">{{ $item->name }}</p>
                                                            <p id="item-meta-{{ $index }}" class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                                                {{ $item->product?->brand ?? 'Sin marca' }} · {{ $unitDefault }} · {{ $locationLabel }}
                                                            </p>
                                                            <span class="item-status mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                                                                <span aria-hidden="true">●</span>
                                                                Incluido
                                                            </span>
                                                            @if($isPendingInventory)
                                                                <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-100">
                                                                    ⚠️ Sin lote en inventario
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </label>
                                                    <input type="hidden" name="items[{{ $index }}][list_item_id]" value="{{ $item->id }}">
                                                    <input type="hidden" name="items[{{ $index }}][unit_size]" value="{{ $item->unit_size ?? 1 }}">
                                                    <input type="hidden" name="items[{{ $index }}][location_id]" value="{{ $locationId }}">
                                                </td>
                                                <td class="py-4 px-2 align-top">
                                                    <label class="sr-only" for="qty-{{ $index }}">Cantidad comprada</label>
                                                    <input id="qty-{{ $index }}" type="number" step="1" min="0" inputmode="numeric" name="items[{{ $index }}][qty]" value="{{ (int) round($qtyDefault) }}" class="{{ $input }} item-input item-qty h-10 w-full min-w-[4.5rem] text-sm bg-white/90 dark:bg-gray-800/60">
                                                </td>
                                                <td class="py-4 px-2 align-top">
                                                    <label class="sr-only" for="unit-{{ $index }}">Unidad</label>
                                                    <input id="unit-{{ $index }}" name="items[{{ $index }}][unit]" value="{{ $unitDefault }}" class="{{ $input }} item-input item-unit h-10 w-full min-w-[4rem] text-sm bg-white/90 dark:bg-gray-800/60">
                                                </td>
                                                <td class="py-4 px-2 align-top">
                                                    <label class="sr-only" for="price-{{ $index }}">Precio unitario</label>
                                                    <input id="price-{{ $index }}" type="number" step="1" min="0" inputmode="numeric" name="items[{{ $index }}][unit_price]" value="{{ (int) round($priceDefault) }}" class="{{ $input }} item-input item-price h-10 w-full min-w-[5rem] text-sm bg-white/90 dark:bg-gray-800/60">
                                                </td>
                                                <td class="py-4 pr-4 pl-2 text-right align-top">
                                                    <span class="item-subtotal text-base font-semibold text-gray-900 dark:text-gray-100" data-initial="{{ $initialSubtotal }}">COP {{ number_format($initialSubtotal, 0, ',', '.') }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </section>

                    <aside class="rounded-lg border border-emerald-100 bg-emerald-50/70 p-5 shadow-inner dark:border-emerald-900/50 dark:bg-emerald-900/20 lg:sticky lg:top-6" role="region" aria-labelledby="purchase-summary-title" aria-describedby="purchase-summary-desc">
                        <h3 id="purchase-summary-title" class="text-lg font-semibold text-emerald-800 dark:text-emerald-200">Resumen de la compra</h3>
                        <p id="purchase-summary-desc" class="text-sm text-emerald-700/80 dark:text-emerald-100/80">Confirma al menos un ítem para activar el botón de guardado.</p>
                        <div class="mt-4 space-y-4">
                            <div>
                                <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-200">
                                    <span>Progreso</span>
                                    <span id="purchase-progress-label">0%</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-emerald-100/70 dark:bg-emerald-900/40">
                                    <span id="purchase-progress-fill" class="block h-full rounded-full bg-emerald-500 transition-[width]" style="width: 0%;"></span>
                                </div>
                            </div>
                            <dl class="grid gap-3 text-sm">
                                <div class="flex items-center justify-between">
                                    <dt>Items seleccionados</dt>
                                    <dd id="purchase-summary-count" role="status" aria-live="polite" class="text-xl font-semibold text-emerald-800 dark:text-emerald-100">0</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt>Valor estimado</dt>
                                    <dd id="purchase-summary-total" role="status" aria-live="polite" class="text-xl font-semibold text-emerald-800 dark:text-emerald-100">COP 0</dd>
                                </div>
                                <div class="flex items-center justify-between text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-200">
                                    <dt>Presupuesto afectado</dt>
                                    <dd class="text-right text-sm font-semibold text-emerald-900 dark:text-emerald-100">{{ $budgetLabel }}</dd>
                                </div>
                                <div class="flex items-center justify-between text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-200">
                                    <dt>Lista origen</dt>
                                    <dd class="text-right text-sm font-semibold text-emerald-900 dark:text-emerald-100">{{ $selectedList->name }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt>Items pendientes</dt>
                                    <dd>{{ $pendingListItems }}</dd>
                                </div>
                            </dl>
                        </div>
                        <p class="mt-4 text-xs text-emerald-800/80 dark:text-emerald-200/80">El total definitivo se calcula con los precios registrados en cada ítem.</p>
                    </aside>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Se actualizará la lista, el inventario y opcionalmente las finanzas.</p>
                    <button type="submit" class="{{ $btnPrimary }} disabled:opacity-60 disabled:cursor-not-allowed">Registrar compra</button>
                </div>
            </form>
        @endif

    </div>

    <div class="fixed inset-x-0 bottom-0 z-30 mx-auto max-w-6xl px-4 pb-4 md:hidden">
        <div class="flex items-center justify-between rounded-lg bg-white/95 p-3 shadow-2xl ring-1 ring-gray-200 dark:bg-gray-900/95 dark:ring-gray-700">
            <a href="{{ route('food.shopping-list.index') }}" class="flex-1 text-center text-xs font-semibold text-gray-600 hover:text-emerald-600">
                🗒️ Lista
            </a>
            <a href="{{ route('food.purchases.index', ['list_id' => $selectedList?->id]) }}" class="flex-1 text-center text-xs font-semibold text-emerald-600">
                🛒 Compras
            </a>
            <a href="{{ route('food.inventory.index') }}" class="flex-1 text-center text-xs font-semibold text-gray-600 hover:text-emerald-600">
                📦 Inventario
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('purchase-form');
            if (!form) return;

            const rows = Array.from(document.querySelectorAll('.item-row'));
            const summaryCount = document.getElementById('purchase-summary-count');
            const summaryTotal = document.getElementById('purchase-summary-total');
            const submitButton = form.querySelector('button[type="submit"]');
            const selectAllBtn = document.getElementById('items-select-all');
            const clearAllBtn = document.getElementById('items-clear-all');
            const progressFill = document.getElementById('purchase-progress-fill');
            const progressLabel = document.getElementById('purchase-progress-label');
            const currencyFormatter = new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });
            const filterButtons = document.querySelectorAll('[data-items-filter]');
            let activeFilter = 'all';

            const setFilterButtonState = () => {
                filterButtons.forEach((button) => {
                    const isActive = button.dataset.itemsFilter === activeFilter;
                    button.classList.toggle('bg-emerald-600', isActive);
                    button.classList.toggle('text-white', isActive);
                    button.classList.toggle('border-emerald-600', isActive);
                    button.classList.toggle('text-gray-700', !isActive);
                    button.classList.toggle('dark:text-gray-100', !isActive);
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            };

            const applyRowFilter = () => {
                rows.forEach((row) => {
                    const isPending = row.dataset.pendingInventory === '1';
                    const shouldShow = activeFilter === 'all' || (activeFilter === 'pending' && isPending);
                    row.classList.toggle('hidden', !shouldShow);
                    row.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
                });
            };

            const styleStatusBadge = (row, isChecked) => {
                const badge = row?.querySelector('.item-status');
                if (!badge) return;
                badge.classList.remove('text-emerald-600', 'text-gray-500');
                if (isChecked) {
                    badge.textContent = 'Incluido';
                    badge.classList.add('text-emerald-600');
                } else {
                    badge.textContent = 'Pendiente';
                    badge.classList.add('text-gray-500');
                }
            };

            const updateRowSubtotal = (row) => {
                const subtotalEl = row?.querySelector('.item-subtotal');
                const qtyInput = row?.querySelector('.item-qty');
                const priceInput = row?.querySelector('.item-price');
                const include = row?.querySelector('.item-include');
                if (!subtotalEl || !qtyInput || !priceInput) return;
                const isChecked = include ? include.checked : true;
                const qty = isChecked ? parseFloat(qtyInput.value) || 0 : 0;
                const price = isChecked ? parseFloat(priceInput.value) || 0 : 0;
                subtotalEl.textContent = currencyFormatter.format(qty * price);
            };

            const recalcSummary = () => {
                let total = 0;
                let count = 0;
                rows.forEach((row) => {
                    const include = row.querySelector('.item-include');
                    const qtyInput = row.querySelector('.item-qty');
                    const priceInput = row.querySelector('.item-price');
                    if (!include || !qtyInput || !priceInput) return;
                    if (!include.checked) return;
                    count += 1;
                    const qty = parseFloat(qtyInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    total += qty * price;
                });

                if (summaryCount) summaryCount.textContent = count;
                if (summaryTotal) summaryTotal.textContent = currencyFormatter.format(total);
                if (submitButton) {
                    submitButton.disabled = count === 0;
                    submitButton.setAttribute('aria-disabled', count === 0 ? 'true' : 'false');
                }
                if (progressFill && progressLabel) {
                    const ratio = rows.length ? Math.round((count / rows.length) * 100) : 0;
                    progressFill.style.width = `${ratio}%`;
                    progressLabel.textContent = `${ratio}%`;
                }
            };

            const toggleRowState = (checkbox) => {
                const row = checkbox.closest('.item-row');
                if (!row) return;
                row.querySelectorAll('.item-input').forEach((input) => {
                    input.disabled = !checkbox.checked;
                });
                row.classList.toggle('opacity-60', !checkbox.checked);
                styleStatusBadge(row, checkbox.checked);
                updateRowSubtotal(row);
            };

            rows.forEach((row) => {
                const checkbox = row.querySelector('.item-include');
                if (!checkbox) return;
                toggleRowState(checkbox);
                checkbox.addEventListener('change', () => {
                    toggleRowState(checkbox);
                    recalcSummary();
                });
                row.querySelectorAll('.item-qty, .item-price').forEach((input) => {
                    input.addEventListener('input', () => {
                        updateRowSubtotal(row);
                        recalcSummary();
                    });
                });
                updateRowSubtotal(row);
            });

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', () => {
                    rows.forEach((row) => {
                        const checkbox = row.querySelector('.item-include');
                        if (!checkbox) return;
                        checkbox.checked = true;
                        toggleRowState(checkbox);
                    });
                    recalcSummary();
                });
            }

            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', () => {
                    rows.forEach((row) => {
                        const checkbox = row.querySelector('.item-include');
                        if (!checkbox) return;
                        checkbox.checked = false;
                        toggleRowState(checkbox);
                    });
                    recalcSummary();
                });
            }

            if (filterButtons.length) {
                setFilterButtonState();
                applyRowFilter();
                filterButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        activeFilter = button.dataset.itemsFilter || 'all';
                        setFilterButtonState();
                        applyRowFilter();
                    });
                });
            }

            recalcSummary();
        });
    </script>
</x-layouts.app>
