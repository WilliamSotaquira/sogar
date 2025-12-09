<x-layouts.app :title="__('Compras de Alimentos')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 transition';
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
        $totalListItems = $selectedList?->items->count() ?? 0;
        $pendingListItems = $selectedList?->items->filter(fn ($item) => empty($item->is_checked))->count() ?? 0;
        $budgetLabel = $selectedList?->budget?->category?->name
            ? $selectedList->budget->category->name . ' · ' . str_pad($selectedList->budget->month, 2, '0', STR_PAD_LEFT) . '/' . $selectedList->budget->year
            : 'Sin presupuesto asignado';
        $pendingInventoryCount = $pendingInventoryCount ?? 0;
        $pendingInventoryPreview = ($pendingInventoryItems ?? collect())->take(3);
    @endphp
    <div class="mx-auto w-full max-w-6xl space-y-4 px-3 pb-28 sm:px-4 md:px-0 md:pb-6">
        <div class="overflow-hidden rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 shadow-lg dark:from-emerald-600 dark:to-teal-700 sm:p-6 md:p-8">
            <div class="flex flex-col gap-3 text-white md:flex-row md:items-start md:justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-white/90 md:text-sm">Gasto de alimentos</p>
                    <h1 class="mb-1 text-2xl font-bold text-white md:text-3xl">Registro de Compras</h1>
                    <p class="text-sm text-white/85">Registra tickets y vincula presupuesto, categoría y wallet.</p>
                    @if($pendingInventoryCount > 0)
                        <span class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1.5 text-xs font-semibold text-white shadow-sm backdrop-blur-sm">
                            <span class="flex-shrink-0">⚠️</span>
                            <span class="truncate">{{ $pendingInventoryCount }} producto{{ $pendingInventoryCount === 1 ? '' : 's' }} pendiente{{ $pendingInventoryCount === 1 ? '' : 's' }} de inventario</span>
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
            <div class="rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-6 shadow-sm dark:border-amber-900/40 dark:from-amber-900/20 dark:to-orange-900/20">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 rounded-full bg-amber-100 p-3 dark:bg-amber-900/40">
                        <svg class="h-6 w-6 text-amber-600 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-200">Sincronización Pendiente</p>
                                <h3 class="text-lg font-bold text-amber-900 dark:text-amber-100">{{ $pendingInventoryCount }} producto{{ $pendingInventoryCount === 1 ? '' : 's' }} sin lote en inventario</h3>
                                <p class="mt-1 text-sm text-amber-700/90 dark:text-amber-100/80">Estos productos están marcados en la lista pero no tienen registro de inventario.</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2">
                            @foreach($pendingInventoryPreview as $pendingItem)
                                <div class="flex flex-col gap-2 rounded-lg border border-amber-100 bg-white/80 px-4 py-3 text-sm shadow-sm dark:border-amber-900/30 dark:bg-amber-900/20 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex-1">
                                        <p class="font-semibold text-amber-900 dark:text-amber-100">{{ $pendingItem->name }}</p>
                                        <p class="text-xs text-amber-700/80 dark:text-amber-200/70">
                                            Marcado {{ optional($pendingItem->checked_at)->diffForHumans() ?? 'sin fecha' }} ·
                                            {{ $pendingItem->qty_to_buy_base }} {{ $pendingItem->unit_base }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Pendiente
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        @if($pendingInventoryCount > $pendingInventoryPreview->count())
                            <p class="mt-3 text-xs text-amber-700/80 dark:text-amber-100/70">
                                ... y {{ $pendingInventoryCount - $pendingInventoryPreview->count() }} producto{{ ($pendingInventoryCount - $pendingInventoryPreview->count()) === 1 ? '' : 's' }} más.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($lists->isEmpty())
            <div class="rounded-xl border-2 border-dashed border-gray-300 bg-white p-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Necesitas una lista activa</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Crea o abre una lista de compras para poder registrar tus productos adquiridos.</p>
                <a href="{{ route('food.shopping-list.index') }}" class="{{ $btnPrimary }} mt-6">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Ver mis listas
                </a>
            </div>
        @else
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900" role="region" aria-labelledby="selected-list-title">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <p id="selected-list-title" class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Lista seleccionada</p>
                        <h2 class="truncate text-xl font-bold text-gray-900 dark:text-gray-100 md:text-2xl">{{ $selectedList->name }}</h2>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <span class="text-xs md:text-sm">Generada {{ optional($selectedList->generated_at)->format('d M Y') ?? '—' }}</span>
                            <span class="hidden md:inline">·</span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                <span class="h-1.5 w-1.5 flex-shrink-0 rounded-full bg-emerald-500"></span>
                                <span class="truncate">{{ strtoupper($selectedList->status ?? 'activo') }}</span>
                            </span>
                        </div>
                    </div>
                    <dl class="grid w-full gap-3 text-sm sm:grid-cols-3 lg:w-auto lg:min-w-[400px]">
                        <div class="overflow-hidden rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-3 dark:from-gray-800/40 dark:to-gray-800/60">
                            <dt class="truncate text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Items totales</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalListItems }}</dd>
                        </div>
                        <div class="overflow-hidden rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 px-4 py-3 dark:from-amber-900/30 dark:to-orange-900/30">
                            <dt class="truncate text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">Pendientes</dt>
                            <dd class="text-2xl font-bold text-amber-800 dark:text-amber-200">{{ $pendingListItems }}</dd>
                        </div>
                        <div class="overflow-hidden rounded-xl bg-gradient-to-br from-blue-50 to-cyan-50 px-4 py-3 dark:from-blue-900/30 dark:to-cyan-900/30">
                            <dt class="truncate text-xs uppercase tracking-wide text-blue-700 dark:text-blue-300">Presupuesto</dt>
                            <dd class="truncate text-xs font-semibold text-blue-900 dark:text-blue-100 md:text-sm">{{ $budgetLabel }}</dd>
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

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-2xl border-2 border-gray-200 bg-gradient-to-br from-white to-gray-50 p-4 shadow-lg dark:border-gray-700 dark:from-gray-800 dark:to-gray-900 md:p-6" role="region" aria-labelledby="purchase-details-title">
                        <div class="mb-4 flex items-center justify-between gap-2">
                            <h3 id="purchase-details-title" class="text-lg font-bold text-gray-900 dark:text-gray-100 md:text-xl">Detalles de la compra</h3>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 text-blue-500 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <p class="mb-5 text-sm text-gray-600 dark:text-gray-400">Define cuándo y dónde hiciste la compra.</p>
                        <div class="space-y-4">
                            <div>
                                <label class="{{ $label }}">Fecha de compra</label>
                                <input type="date" name="occurred_on" value="{{ now()->timezone('America/Bogota')->format('Y-m-d') }}" class="{{ $input }} transition-all duration-200 hover:border-blue-400 focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="{{ $label }}">Lugar / Proveedor</label>
                                <input name="vendor" placeholder="Ej: Supermercado Central" class="{{ $input }} transition-all duration-200 hover:border-blue-400 focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="{{ $label }}">Nota</label>
                                <textarea name="note" rows="2" class="{{ $input }} resize-none transition-all duration-200 hover:border-blue-400 focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border-2 border-gray-200 bg-gradient-to-br from-white to-gray-50 p-4 shadow-lg dark:border-gray-700 dark:from-gray-800 dark:to-gray-900 md:p-6" role="region" aria-labelledby="payment-title">
                        <div class="mb-4 flex items-center justify-between gap-2">
                            <h3 id="payment-title" class="text-lg font-bold text-gray-900 dark:text-gray-100 md:text-xl">Pago y control</h3>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 text-emerald-500 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <p class="mb-5 text-sm text-gray-600 dark:text-gray-400">Asocia método, transacción y opciones financieras.</p>
                        <div class="space-y-4">
                            <div>
                                <label class="{{ $label }}">Presupuesto</label>
                                <select name="budget_id" class="{{ $input }} transition-all duration-200 hover:border-emerald-400 focus:ring-2 focus:ring-emerald-500">
                                    <option value="">Sin presupuesto</option>
                                    @foreach($budgets as $budget)
                                        <option value="{{ $budget->id }}">
                                            {{ $budget->category->name ?? 'Sin categoría' }} ·
                                            {{ str_pad($budget->month, 2, '0', STR_PAD_LEFT) }}/{{ $budget->year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="{{ $label }}">Bolsillo / Wallet (opcional)</label>
                                <select name="wallet_id" class="{{ $input }} transition-all duration-200 hover:border-emerald-400 focus:ring-2 focus:ring-emerald-500">
                                    <option value="">Sin bolsillo</option>
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="{{ $label }}">Número de transacción</label>
                                <input name="receipt_number" class="{{ $input }} transition-all duration-200 hover:border-emerald-400 focus:ring-2 focus:ring-emerald-500" placeholder="Factura, ticket o referencia">
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-gradient-to-br from-emerald-50 to-cyan-50 px-4 py-3.5 shadow-sm dark:from-emerald-900/20 dark:to-cyan-900/20">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Impactar finanzas</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Genera movimiento contable al guardar.</p>
                                </div>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                    <input type="checkbox" name="impact_finanzas" value="1" class="h-5 w-5 rounded border-gray-300 text-emerald-600 transition-transform hover:scale-110 focus:ring-2 focus:ring-emerald-500">
                                    <span class="font-medium">Sí</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <section class="overflow-hidden rounded-2xl border-2 border-gray-200 bg-white p-4 shadow-lg dark:border-gray-700 dark:bg-gray-800 md:p-6" role="region" aria-labelledby="confirm-items-title" aria-describedby="confirm-items-desc">
                    <div class="mb-6">
                        <div class="mb-4">
                                <h3 id="confirm-items-title" class="text-lg font-bold text-gray-900 dark:text-gray-100 md:text-xl">Confirmar ítems</h3>
                                <p id="confirm-items-desc" class="text-sm text-gray-600 dark:text-gray-400">Marca qué productos realmente compraste y ajusta cantidades o precios.</p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Badge de items pendientes -->
                                <div class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-gray-100 to-gray-200 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500 shadow-sm dark:from-gray-700 dark:to-gray-800 dark:text-gray-400">
                                    <span class="tabular-nums">{{ $pendingListItems }}</span>
                                    <span class="hidden sm:inline">ítems pendientes</span>
                                    <span class="sm:hidden">pend.</span>
                                </div>

                                <!-- Separador visual -->
                                <div class="hidden h-6 w-px bg-gray-300 dark:bg-gray-600 sm:block" aria-hidden="true"></div>

                                <!-- Botones de acción -->
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" id="items-select-all" aria-label="Seleccionar todos los items" class="inline-flex touch-target items-center gap-1.5 rounded-full border-2 border-emerald-200 bg-emerald-50 px-4 py-1.5 text-[11px] font-semibold normal-case text-emerald-700 transition-all hover:border-emerald-500 hover:bg-emerald-100 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="hidden sm:inline">Seleccionar todo</span>
                                        <span class="sm:hidden">Todos</span>
                                    </button>
                                    <button type="button" id="items-clear-all" aria-label="Limpiar selección de items" class="inline-flex touch-target items-center gap-1.5 rounded-full border-2 border-rose-200 bg-rose-50 px-4 py-1.5 text-[11px] font-semibold normal-case text-rose-700 transition-all hover:border-rose-500 hover:bg-rose-100 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 dark:border-rose-800 dark:bg-rose-900/30 dark:text-rose-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span class="hidden sm:inline">Limpiar selección</span>
                                        <span class="sm:hidden">Limpiar</span>
                                    </button>
                                </div>

                                @if($pendingInventoryCount > 0)
                                    <!-- Separador visual -->
                                    <div class="hidden h-6 w-px bg-gray-300 dark:bg-gray-600 sm:block" aria-hidden="true"></div>

                                    <!-- Filtros de inventario -->
                                    <div class="inline-flex overflow-hidden rounded-full border-2 border-amber-200 bg-amber-50 text-[11px] font-semibold dark:border-amber-800 dark:bg-amber-900/30" role="group" aria-label="Filtrar ítems por estado de inventario">
                                        <button type="button" data-items-filter="all" aria-pressed="true" class="pending-filter-btn inline-flex items-center gap-1.5 px-4 py-1.5 text-gray-700 transition-colors hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-inset dark:text-gray-200 dark:hover:bg-amber-900/50">
                                            <span>Todos</span>
                                        </button>
                                        <button type="button" data-items-filter="pending" aria-pressed="false" class="pending-filter-btn inline-flex items-center gap-1.5 border-l-2 border-amber-300 px-4 py-1.5 text-gray-700 transition-colors hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-inset dark:border-amber-700 dark:text-gray-200 dark:hover:bg-amber-900/50">
                                            <span class="hidden sm:inline">Pendientes inv.</span>
                                            <span class="sm:hidden">Pend.</span>
                                            <span class="rounded-full bg-amber-200 px-2 py-0.5 text-[10px] font-bold text-amber-900 dark:bg-amber-700 dark:text-amber-100">{{ $pendingInventoryCount }}</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($listItems->isEmpty())
                            <div class="mt-4 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-3 h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="font-medium">La lista seleccionada no tiene productos para confirmar.</p>
                            </div>
                        @else
                            <div class="-mx-4 overflow-x-auto md:mx-0">
                                <div class="inline-block min-w-full align-middle">
                                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900">
                                                <tr>
                                                    <th scope="col" class="whitespace-nowrap py-3 pl-4 pr-2 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 md:py-4 md:pl-6 md:text-[11px]">Ítem</th>
                                                    <th scope="col" class="whitespace-nowrap px-2 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 md:py-4 md:text-[11px]">Cantidad</th>
                                                    <th scope="col" class="whitespace-nowrap px-2 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 md:py-4 md:text-[11px]">Unidad</th>
                                                    <th scope="col" class="whitespace-nowrap px-2 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 md:py-4 md:text-[11px]">Precio unit.</th>
                                                    <th scope="col" class="whitespace-nowrap py-3 pl-2 pr-4 text-right text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 md:py-4 md:pr-6 md:text-[11px]">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900">
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
                                                <td class="py-3 pl-4 pr-2 align-top md:py-4 md:pl-6">
                                                    <label class="flex items-start gap-2 text-gray-700 dark:text-gray-200">
                                                        <input type="checkbox" name="items[{{ $index }}][include]" value="1" class="item-include mt-0.5 h-5 w-5 flex-shrink-0 rounded border-gray-300 text-emerald-600 transition-transform hover:scale-110 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2" checked aria-describedby="item-meta-{{ $index }}">
                                                        <div class="min-w-0 flex-1">
                                                            <p id="item-title-{{ $index }}" class="break-words font-semibold text-gray-900 dark:text-gray-100">{{ $item->name }}</p>
                                                            <p id="item-meta-{{ $index }}" class="mt-1 break-words text-[10px] text-gray-500 dark:text-gray-400 md:text-[11px]">
                                                                <span class="inline-block">{{ $item->product?->brand ?? 'Sin marca' }}</span>
                                                                <span class="mx-1" aria-hidden="true">·</span>
                                                                <span class="inline-block">{{ $unitDefault }}</span>
                                                                <span class="mx-1" aria-hidden="true">·</span>
                                                                <span class="inline-block">{{ $locationLabel }}</span>
                                                            </p>
                                                            <span class="item-status mt-1 inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 md:text-[11px]">
                                                                <span aria-hidden="true">●</span>
                                                                <span>Incluido</span>
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
                                                <td class="px-2 py-3 align-top md:py-4">
                                                    <label class="sr-only" for="qty-{{ $index }}">Cantidad comprada</label>
                                                    <input id="qty-{{ $index }}" type="number" step="1" min="0" inputmode="numeric" name="items[{{ $index }}][qty]" value="{{ (int) round($qtyDefault) }}" class="{{ $input }} item-input item-qty h-10 w-full min-w-[3.5rem] bg-white/90 text-sm focus:z-10 dark:bg-gray-800/60 md:min-w-[4.5rem]" aria-label="Cantidad de {{ $item->name }}">
                                                </td>
                                                <td class="px-2 py-3 align-top md:py-4">
                                                    <label class="sr-only" for="unit-{{ $index }}">Unidad</label>
                                                    <input id="unit-{{ $index }}" name="items[{{ $index }}][unit]" value="{{ $unitDefault }}" class="{{ $input }} item-input item-unit h-10 w-full min-w-[3rem] bg-white/90 text-sm focus:z-10 dark:bg-gray-800/60 md:min-w-[4rem]" aria-label="Unidad de medida de {{ $item->name }}">
                                                </td>
                                                <td class="px-2 py-3 align-top md:py-4">
                                                    <label class="sr-only" for="price-{{ $index }}">Precio unitario</label>
                                                    <input id="price-{{ $index }}" type="number" step="1" min="0" inputmode="numeric" name="items[{{ $index }}][unit_price]" value="{{ (int) round($priceDefault) }}" class="{{ $input }} item-input item-price h-10 w-full min-w-[4rem] bg-white/90 text-sm focus:z-10 dark:bg-gray-800/60 md:min-w-[5rem]" aria-label="Precio unitario de {{ $item->name }}">
                                                </td>
                                                <td class="py-3 pl-2 pr-4 text-right align-top md:py-4 md:pr-6">
                                                    <span class="item-subtotal whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100 md:text-base" data-initial="{{ $initialSubtotal }}" aria-label="Subtotal de {{ $item->name }}">COP {{ number_format($initialSubtotal, 0, ',', '.') }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                </section>

                <aside class="overflow-hidden rounded-2xl border-2 border-emerald-200 bg-gradient-to-br from-emerald-50 to-cyan-50 p-4 shadow-lg dark:border-emerald-800 dark:from-emerald-900/30 dark:to-cyan-900/30 md:p-6" role="complementary" aria-labelledby="purchase-summary-title" aria-describedby="purchase-summary-desc">
                        <div class="mb-4 flex items-center justify-between gap-2">
                            <h3 id="purchase-summary-title" class="text-lg font-bold text-emerald-900 dark:text-emerald-100 md:text-xl">Resumen de la compra</h3>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p id="purchase-summary-desc" class="text-sm text-emerald-800 dark:text-emerald-200">Confirma al menos un ítem para activar el botón de guardado.</p>
                        <div class="mt-6 space-y-4">
                            <div>
                                <div class="mb-2 flex items-center justify-between text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-200">
                                    <span>Progreso</span>
                                    <span id="purchase-progress-label" class="text-sm">0%</span>
                                </div>
                                <div class="h-3 overflow-hidden rounded-full bg-emerald-200/50 shadow-inner dark:bg-emerald-900/50">
                                    <span id="purchase-progress-fill" class="block h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500 shadow-sm transition-[width] duration-500" style="width: 0%;"></span>
                                </div>
                            </div>
                            <dl class="space-y-3 text-sm">
                                <div class="flex items-center justify-between gap-2 overflow-hidden rounded-lg bg-white/50 px-4 py-3 backdrop-blur-sm dark:bg-gray-900/30">
                                    <dt class="min-w-0 truncate font-medium text-emerald-800 dark:text-emerald-200">Items seleccionados</dt>
                                    <dd id="purchase-summary-count" role="status" aria-live="polite" class="flex-shrink-0 text-xl font-bold text-emerald-900 dark:text-emerald-100 md:text-2xl">0</dd>
                                </div>
                                <div class="flex items-center justify-between gap-2 overflow-hidden rounded-lg bg-white/50 px-4 py-3 backdrop-blur-sm dark:bg-gray-900/30">
                                    <dt class="min-w-0 truncate font-medium text-emerald-800 dark:text-emerald-200">Valor estimado</dt>
                                    <dd id="purchase-summary-total" role="status" aria-live="polite" class="flex-shrink-0 whitespace-nowrap text-xl font-bold text-emerald-900 dark:text-emerald-100 md:text-2xl">COP 0</dd>
                                </div>
                                <div class="flex items-center justify-between gap-2 border-t-2 border-emerald-200 pt-3 text-xs uppercase tracking-wide text-emerald-700 dark:border-emerald-800 dark:text-emerald-300">
                                    <dt class="min-w-0 truncate">Presupuesto afectado</dt>
                                    <dd class="flex-shrink-0 truncate text-right text-xs font-bold text-emerald-900 dark:text-emerald-100 md:text-sm" title="{{ $budgetLabel }}">{{ $budgetLabel }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-2 text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                                    <dt class="min-w-0 truncate">Lista origen</dt>
                                    <dd class="flex-shrink-0 truncate text-right text-xs font-bold text-emerald-900 dark:text-emerald-100 md:text-sm" title="{{ $selectedList->name }}">{{ $selectedList->name }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-2 text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                                    <dt class="min-w-0 truncate">Items pendientes</dt>
                                    <dd class="flex-shrink-0 rounded-full bg-amber-200 px-2.5 py-1 text-xs font-bold text-amber-900 dark:bg-amber-800 dark:text-amber-100">{{ $pendingListItems }}</dd>
                                </div>
                            </dl>
                        </div>
                        <p class="mt-5 rounded-lg bg-white/60 p-3 text-xs font-medium text-emerald-800 backdrop-blur-sm dark:bg-gray-900/30 dark:text-emerald-200">💡 El total definitivo se calcula con los precios registrados en cada ítem.</p>
                </aside>

                <div class="flex flex-col gap-4 overflow-hidden rounded-2xl border-2 border-emerald-200 bg-gradient-to-br from-emerald-50 to-cyan-50 p-4 shadow-lg dark:border-emerald-800 dark:from-emerald-900/30 dark:to-cyan-900/30 sm:flex-row sm:items-center sm:justify-between md:p-6">
                    <div class="flex items-start gap-3 sm:items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0 text-emerald-600 dark:text-emerald-400 md:h-8 md:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="min-w-0 text-sm text-emerald-800 dark:text-emerald-200">
                            <span class="font-semibold">Se actualizará</span> la lista, el inventario y opcionalmente las finanzas.
                        </p>
                    </div>
                    <button type="submit" class="{{ $btnPrimary }} w-full touch-target px-6 py-3 text-base shadow-md transition-all hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:shadow-md sm:w-auto md:px-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 inline-block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Registrar compra</span>
                    </button>
                </div>
            </form>
        @endif

    </div>

    <div id="floating-nav" class="fixed right-3 top-1/2 z-30 -translate-y-1/2 transition-transform duration-300 md:hidden">
        <div class="flex flex-col items-center gap-2 rounded-xl bg-white/80 p-2 shadow-lg ring-1 ring-gray-200/50 backdrop-blur-sm dark:bg-gray-900/80 dark:ring-gray-700/50">
            <button onclick="toggleFloatingNav()"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-lg transition-all hover:bg-gray-50 dark:hover:bg-gray-800/50"
                    title="Ocultar menú">
                ✕
            </button>
            <button onclick="openQuickProductModal()"
                    class="flex h-10 w-10 items-center justify-center rounded-lg text-xl transition-all hover:bg-emerald-50 hover:scale-105 dark:hover:bg-emerald-900/20"
                    title="Producto rápido">
                ➕
            </button>
            <a href="{{ route('food.shopping-list.index') }}"
               class="flex h-10 w-10 items-center justify-center rounded-lg text-xl transition-all hover:bg-gray-50 hover:scale-105 dark:hover:bg-gray-800/50"
               title="Lista de compras">
                🗒️
            </a>
            <a href="{{ route('food.inventory.index') }}"
               class="flex h-10 w-10 items-center justify-center rounded-lg text-xl transition-all hover:bg-gray-50 hover:scale-105 dark:hover:bg-gray-800/50"
               title="Inventario">
                📦
            </a>
            <a href="{{ route('food.products.index') }}"
               class="flex h-10 w-10 items-center justify-center rounded-lg text-xl transition-all hover:bg-gray-50 hover:scale-105 dark:hover:bg-gray-800/50"
               title="Catálogo de productos">
                🥫
            </a>
            <a href="{{ route('food.locations.index') }}"
               class="flex h-10 w-10 items-center justify-center rounded-lg text-xl transition-all hover:bg-gray-50 hover:scale-105 dark:hover:bg-gray-800/50"
               title="Ubicaciones">
                📍
            </a>
        </div>
    </div>

    <button id="floating-nav-trigger"
            onclick="toggleFloatingNav()"
            class="fixed right-3 top-1/2 z-30 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-lg bg-emerald-600/90 text-xl text-white shadow-lg backdrop-blur-sm transition-all hover:bg-emerald-700 hover:scale-105 md:hidden"
            title="Mostrar menú">
        ☰
    </button>

    <script>
        function toggleFloatingNav() {
            const nav = document.getElementById('floating-nav');
            const trigger = document.getElementById('floating-nav-trigger');

            if (nav.classList.contains('translate-x-full')) {
                nav.classList.remove('translate-x-full');
                trigger.classList.add('hidden');
                trigger.classList.remove('flex');
            } else {
                nav.classList.add('translate-x-full');
                trigger.classList.remove('hidden');
                trigger.classList.add('flex');
            }
        }

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

    <x-quick-product-modal :locations="$locations ?? []" :types="$types ?? []" />
</x-layouts.app>
