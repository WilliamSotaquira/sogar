@php
    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
    $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
    $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';

    $budgets = \App\Models\Budget::where('user_id', auth()->id())
        ->with('category')
        ->where('month', now()->month)
        ->where('year', now()->year)
        ->get();

    $types = \App\Models\FoodType::where('user_id', auth()->id())->where('is_active', true)->orderBy('sort_order')->get();
    $locations = \App\Models\FoodLocation::where('user_id', auth()->id())->orderBy('sort_order')->get();
@endphp

<x-layouts.app :title="__('Lista de compra')">
    <div class="mx-auto w-full max-w-6xl space-y-4 px-3 pb-20 sm:px-6 md:px-8 md:pb-6">
        {{-- Encabezado (simplificado) --}}
        <header class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                        Compras
                    </p>
                    <h1 class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-50 sm:text-2xl">
                        Lista de compras
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Agrega productos y marca lo comprado. El presupuesto es opcional.
                    </p>
                </div>

                @if($list)
                    @php
                        $checkedCount = $list->items->where('is_checked', true)->count();
                        $totalCount = $list->items->count();
                        $pendingCount = max(0, $totalCount - $checkedCount);
                        $progress = $totalCount > 0 ? (int) round(($checkedCount / $totalCount) * 100) : 0;
                    @endphp
                    <dl class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-600 dark:text-gray-300">Pendientes</dt>
                            <dd class="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-gray-50">{{ $pendingCount }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-600 dark:text-gray-300">Completados</dt>
                            <dd class="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-gray-50">{{ $checkedCount }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-600 dark:text-gray-300">Estimado</dt>
                            <dd class="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-gray-50">${{ number_format($list->estimated_budget, 0, ',', '.') }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-neutral-900 dark:ring-gray-800">
                            <dt class="text-xs font-medium text-gray-600 dark:text-gray-300">Real</dt>
                            <dd class="mt-1 text-lg font-semibold tabular-nums {{ $list->actual_total > 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-900 dark:text-gray-50' }}">${{ number_format($list->actual_total ?: 0, 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                @endif
            </div>

            @if($list)
                <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-600 dark:text-gray-300">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-gray-100 px-2 py-1 font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            {{ $list->name }}
                        </span>
                        @if($list->budget)
                            <span class="rounded-full bg-emerald-50 px-2 py-1 font-semibold text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200">
                                Presupuesto: {{ $list->budget->category->name }}
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('food.shopping-list.all') }}"
                       class="font-semibold text-emerald-700 underline decoration-emerald-200 underline-offset-2 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-emerald-300 dark:hover:text-emerald-200">
                        Ver mis listas
                    </a>
                </div>
            @endif
        </header>

        {{-- Status Messages --}}
        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100" role="status" aria-live="polite">
                {{ session('status') }}
            </div>
        @endif

        @if($list)
            <nav class="md:hidden sticky top-3 z-20 -mx-4 px-4" aria-label="Filtrar listas">
                <div class="rounded-lg bg-white/90 backdrop-blur ring-1 ring-gray-100 shadow-md dark:bg-gray-900/90 dark:ring-gray-800">
                    <div class="flex text-sm font-semibold" role="tablist" aria-label="Estado de listas">
                        <a href="{{ route('food.shopping-list.index', ['status' => 'active']) }}"
                           class="flex-1 px-4 py-3 text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 {{ ($statusFilter ?? 'active') === 'active' ? 'text-emerald-700 dark:text-emerald-300 border-b-2 border-emerald-500' : 'text-gray-600 dark:text-gray-300' }}"
                           aria-current="{{ ($statusFilter ?? 'active') === 'active' ? 'page' : 'false' }}">
                            Activas <span class="tabular-nums">({{ $activeCount ?? 0 }})</span>
                        </a>
                        <a href="{{ route('food.shopping-list.index', ['status' => 'completed']) }}"
                           class="flex-1 px-4 py-3 text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 {{ ($statusFilter ?? 'active') === 'completed' ? 'text-emerald-700 dark:text-emerald-300 border-b-2 border-emerald-500' : 'text-gray-600 dark:text-gray-300' }}"
                           aria-current="{{ ($statusFilter ?? 'active') === 'completed' ? 'page' : 'false' }}">
                            Completadas <span class="tabular-nums">({{ $completedCount ?? 0 }})</span>
                        </a>
                    </div>
                    <div class="flex items-center justify-between px-4 py-2 text-xs text-gray-600 dark:text-gray-300">
                        <span>Pendientes: <span class="font-semibold tabular-nums">{{ $pendingItems ?? 0 }}</span></span>
                        <span class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-300" aria-label="Moneda COP">
                            <span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                            COP
                        </span>
                    </div>
                </div>
            </nav>
        @endif

        <div class="grid gap-4 md:grid-cols-3">
            {{-- Main Content --}}
            <div class="md:col-span-2 space-y-4" data-shopping-main>
                @if(!$list)
                    {{-- Generar Nueva Lista (simplificado) --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h2 class="text-md font-semibold text-gray-900 dark:text-gray-50 mb-3">Generar nueva lista</h2>

                        <form method="POST" action="{{ route('food.shopping-list.generate') }}" class="space-y-3" aria-label="Generar lista de compras">
                            @csrf
                            <div>
                                <label class="{{ $label }}" for="list-name">Nombre</label>
                                <input id="list-name" type="text" name="name" placeholder="Ej: Compra semanal" class="{{ $input }}" value="{{ old('name', 'Compra ' . now()->format('d/m')) }}">
                            </div>

                            <div data-list-type-field>
                                <div class="flex items-end justify-between gap-3">
                                    <label class="{{ $label }}">
                                        Tipo <span class="text-rose-500">*</span>
                                    </label>
                                    <button type="button"
                                            class="text-xs font-semibold text-emerald-700 underline decoration-emerald-200 underline-offset-2 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-emerald-300 dark:hover:text-emerald-200"
                                            data-list-type-toggle
                                            aria-expanded="false"
                                            aria-controls="list-type-panel">
                                        Agregar tipo
                                    </button>
                                </div>

                                <select name="list_type" required class="{{ $input }}" data-list-type-select>
                                    @foreach(($listTypes ?? collect()) as $type)
                                        <option value="{{ $type->slug }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>

                                <div id="list-type-panel" class="hidden" data-list-type-panel>
                                    <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                                        <input type="text"
                                               class="h-11 w-full flex-1 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                               placeholder="Nuevo tipo (ej: Mascotas)"
                                               maxlength="50"
                                               data-list-type-input>
                                        <div class="flex gap-2">
                                            <button type="button"
                                                    class="h-11 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400"
                                                    data-list-type-add>
                                                Guardar
                                            </button>
                                            <button type="button"
                                                    class="h-11 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700"
                                                    data-list-type-cancel>
                                                Cancelar
                                            </button>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" role="status" aria-live="polite" aria-atomic="true" data-list-type-status></p>
                                </div>

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ayuda a organizar tus listas.</p>
                            </div>

                            <details class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-neutral-900">
                                <summary class="cursor-pointer select-none text-sm font-semibold text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-gray-200">
                                    Opciones avanzadas
                                </summary>

                                <div class="mt-3 space-y-3">
                                    <div>
                                        <label class="{{ $label }}">Presupuesto <span class="text-xs font-normal text-gray-500 dark:text-gray-400">(opcional)</span></label>
                                        <select name="budget_id" class="{{ $input }}">
                                            <option value="">Sin presupuesto</option>
                                            @foreach($budgets as $budget)
                                                <option value="{{ $budget->id }}">
                                                    {{ $budget->category->name }} - ${{ number_format($budget->amount, 0, ',', '.') }}
                                                    ({{ now()->monthName }} {{ now()->year }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($budgets->isEmpty())
                                            <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                                No tienes presupuestos este mes (opcional). Puedes crearlos en
                                                <a href="{{ route('budgets.index') }}" class="underline">Presupuestos</a>.
                                            </p>
                                        @endif
                                    </div>

                                    <div class="grid gap-3 md:grid-cols-3">
                                        <div>
                                            <label class="{{ $label }}">Fecha estimada</label>
                                            <input type="date" name="expected_purchase_on" value="{{ now()->addDays(3)->format('Y-m-d') }}" class="{{ $input }}">
                                        </div>
                                        <div>
                                            <label class="{{ $label }}">Horizonte (días)</label>
                                            <input type="number" name="horizon_days" min="1" max="30" value="7" class="{{ $input }}">
                                        </div>
                                        <div>
                                            <label class="{{ $label }}">Personas</label>
                                            <input type="number" name="people_count" min="1" max="10" value="3" class="{{ $input }}">
                                        </div>
                                    </div>
                                </div>
                            </details>

                            <div class="flex justify-end pt-1">
                                <button type="submit" class="{{ $btnPrimary }}">
                                    Generar lista
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Items de la Lista --}}
                @if($list)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <h2 class="text-md font-semibold text-gray-900 dark:text-gray-50">
                                    Items <span class="text-xs font-normal text-gray-500 dark:text-gray-400">({{ $checkedCount }}/{{ $totalCount }})</span>
                                </h2>
                                <div class="w-28">
                                    <div class="flex items-center justify-between text-[11px] text-gray-600 dark:text-gray-300">
                                        <span id="list-progress-label">Progreso</span>
                                        <span class="tabular-nums">{{ $progress }}%</span>
                                    </div>
                                    <div role="progressbar" aria-labelledby="list-progress-label" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}"
                                         class="mt-1 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-full bg-emerald-500 transition-all" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold tabular-nums text-emerald-700 dark:text-emerald-300">${{ number_format($list->actual_total ?: $list->estimated_budget, 0, ',', '.') }}</span>
                                <button type="button" id="toggle-store-mode" aria-pressed="false" class="px-3 py-1 text-xs font-semibold rounded-lg border border-blue-500 text-blue-600 hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:hover:bg-blue-900/20 dark:text-blue-300">
                                    Modo tienda
                                </button>
                            </div>
                        </div>

                        {{-- Agregar Item --}}
                        <form class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 mb-4" data-add-item-form aria-label="Agregar item rápido">
                            <div class="grid gap-2 md:grid-cols-5 items-center">
                                <label class="sr-only" for="search-product-input">Buscar producto</label>
                                <input type="text"
                                       id="search-product-input"
                                       placeholder="Buscar producto…"
                                       autocomplete="off"
                                       aria-describedby="search-status"
                                       class="h-10 rounded-lg border border-gray-200 px-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 md:col-span-2">

                                <label class="sr-only" for="quick-qty">Cantidad</label>
                                <input type="number"
                                       id="quick-qty"
                                       placeholder="Cant."
                                       value="1"
                                       min="1"
                                       inputmode="numeric"
                                       class="h-10 rounded-lg border border-gray-200 px-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">

                                <button type="button"
                                        id="scan-product-btn"
                                        class="h-10 rounded-lg border border-emerald-500 bg-emerald-50 px-3 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                                        aria-label="Escanear código de barras">
                                    Escanear
                                </button>
                                <button type="submit"
                                        id="quick-add-btn"
                                        class="h-10 rounded-lg bg-emerald-600 px-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                                    Agregar
                                </button>
                            </div>
                            <p id="search-status" class="mt-2 text-xs text-gray-600 dark:text-gray-300" role="status" aria-live="polite" aria-atomic="true"></p>
                        </form>

                        {{-- Lista de Items --}}
                        <div id="items-container" class="space-y-2 max-h-[600px] overflow-y-auto">
                            @forelse($list->items as $item)
                                @php
                                    $metadata = $item->metadata ?? [];
                                    $inventoryBatchId = $metadata['inventory_batch_id'] ?? null;
                                    $addedAtLabel = isset($metadata['added_at'])
                                        ? \Carbon\Carbon::parse($metadata['added_at'])->timezone('America/Bogota')->format('d/m H:i')
                                        : null;
                                @endphp
                                <div class="rounded-lg border {{ $item->is_checked ? 'border-emerald-200 bg-emerald-50/30 dark:border-emerald-800 dark:bg-emerald-900/10' : 'border-gray-100 dark:border-gray-800' }} p-3">
                                    <div class="flex items-start gap-3">
                                        {{-- Checkbox --}}
                                        <button type="button"
                                                onclick="toggleItem({{ $list->id }}, {{ $item->id }}, {{ $item->is_checked ? 0 : 1 }})"
                                                aria-pressed="{{ $item->is_checked ? 'true' : 'false' }}"
                                                aria-label="{{ $item->is_checked ? 'Desmarcar' : 'Marcar' }} {{ $item->product?->name ?? $item->name }} como comprado"
                                                class="mt-1 h-6 w-6 flex-shrink-0 rounded-md border transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 {{ $item->is_checked ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-gray-300 dark:border-gray-700 hover:border-emerald-400' }}">
                                            @if($item->is_checked)
                                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        </button>

                                        {{-- Item Info --}}
                                        <div class="flex-1 space-y-1">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    @php
                                                        // Mostrar nombre del producto si existe, sino el nombre del item
                                                        $displayName = $item->product?->name ?? $item->name;
                                                        // Si el nombre parece un código de barras (solo números y largo), mostrar indicador
                                                        $looksLikeBarcode = preg_match('/^\d{8,14}$/', $item->name);
                                                    @endphp
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 {{ $item->is_checked ? 'line-through opacity-60' : '' }}">
                                                        {{ $displayName }}
                                                        @if($item->product?->brand)
                                                            <span class="font-normal text-gray-500">· {{ $item->product->brand }}</span>
                                                        @endif
                                                    </p>
                                                    @if(!$item->product && $looksLikeBarcode)
                                                        <p class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                                            <span>📦</span> Código: {{ $item->name }} - <button type="button" onclick="showCreateProductModal('', {{ $item->id }})" class="underline hover:text-amber-700">Catalogar</button>
                                                        </p>
                                                    @elseif(!$item->product)
                                                        <p class="text-xs text-amber-600 dark:text-amber-400">⚠️ No catalogado</p>
                                                    @endif
                                                </div>
                                                <div class="flex flex-col items-end gap-1 text-right text-xs">
                                                    @if($item->low_stock_alert)
                                                        <span class="rounded-full px-2 py-1 bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-100">
                                                            ⚠️ Stock bajo
                                                        </span>
                                                    @endif
                                                    @if($item->is_checked)
                                                        @if($inventoryBatchId)
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-1 font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                                                                ✅ En inventario
                                                                @if($addedAtLabel)
                                                                    <span class="font-normal text-emerald-600/80 dark:text-emerald-200/80">{{ $addedAtLabel }}</span>
                                                                @endif
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-1 font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-200">
                                                                ⚠️ Pendiente inventario
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="rounded-full bg-gray-100 px-2 py-1 font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">En lista</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                                Cantidad: <span class="font-semibold">{{ $item->qty_to_buy_base }}</span> {{ $item->unit_base }}
                                            </p>
                                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                                Precio:
                                                @if($item->actual_price)
                                                    <span class="font-semibold text-emerald-700 dark:text-emerald-300">${{ number_format($item->actual_price, 0, ',', '.') }}</span>
                                                    @if($item->vendor_name)
                                                        <span class="text-gray-500 dark:text-gray-400">({{ $item->vendor_name }})</span>
                                                    @endif
                                                @elseif(!is_null($item->estimated_price))
                                                    <span class="font-semibold text-gray-900 dark:text-gray-50">${{ number_format($item->estimated_price, 0, ',', '.') }}</span>
                                                    <span class="text-gray-500 dark:text-gray-400">(estimado)</span>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">Sin precio</span>
                                                @endif
                                            </p>
                                        </div>

                                        {{-- Actions --}}
                                        @if(!$item->is_checked)
                                            <div class="flex gap-2">
                                                @if(!$item->product)
                                                    <button type="button"
                                                            onclick="showCreateProductModal('{{ $item->name }}', {{ $item->id }})"
                                                            class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 px-2 py-1 rounded hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:hover:bg-blue-900/20"
                                                            title="Crear en catálogo">
                                                        📝 Crear
                                                    </button>
                                                @endif
                                                <button type="button"
                                                        onclick="showPriceModal({{ $list->id }}, {{ $item->id }}, '{{ $item->name }}', {{ $item->qty_to_buy_base }})"
                                                        class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 px-2 py-1 rounded hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:hover:bg-blue-900/20"
                                                        aria-label="Registrar precio para {{ $displayName }}">
                                                    💰 Precio
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-8">La lista está vacía. Agrega productos arriba.</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4" data-shopping-sidebar>
                {{-- Resumen --}}
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50 mb-3">Resumen</h3>
                    @if($list)
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Estimado:</span>
                                <span class="font-semibold">${{ number_format($list->estimated_budget, 0, ',', '.') }}</span>
                            </div>
                            @if($list->actual_total > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Real:</span>
                                    <span class="font-semibold text-emerald-600">${{ number_format($list->actual_total, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-300">Diferencia:</span>
                                    <span class="font-semibold {{ $list->actual_total > $list->estimated_budget ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $list->actual_total > $list->estimated_budget ? '+' : '' }}${{ number_format($list->actual_total - $list->estimated_budget, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                            @if($list->budget)
                                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-300">Presupuesto disponible:</span>
                                    <span class="font-semibold">${{ number_format($list->budget->amount - $list->actual_total, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Genera una lista para ver el resumen.</p>
                    @endif
                </div>

                {{-- Listas Recientes --}}
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50 mb-3">Listas recientes</h3>
                    <div class="space-y-2">
                        @forelse(($recentLists ?? collect())->take(5) as $recent)
                            <div class="flex justify-between items-center text-sm">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ Str::limit($recent->name, 20) }}</p>
                                    <p class="text-xs text-gray-500">{{ $recent->generated_at?->format('d/m/Y') }}</p>
                                </div>
                                <a href="{{ route('food.shopping-list.show', $recent) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                                    Ver
                                </a>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Sin listas anteriores.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Acción flotante (simplificada) --}}
    <button type="button"
            onclick="openQuickProductModal()"
            class="fixed bottom-6 right-4 z-30 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-600 text-xl font-semibold text-white shadow-lg transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 md:hidden"
            aria-label="Agregar producto rápido"
            title="Producto rápido">
        +
    </button>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const fields = document.querySelectorAll('[data-list-type-field]');

                const initField = (field) => {
                    const select = field.querySelector('[data-list-type-select]');
                    const toggle = field.querySelector('[data-list-type-toggle]');
                    const panel = field.querySelector('[data-list-type-panel]');
                    const input = field.querySelector('[data-list-type-input]');
                    const addBtn = field.querySelector('[data-list-type-add]');
                    const cancelBtn = field.querySelector('[data-list-type-cancel]');
                    const status = field.querySelector('[data-list-type-status]');

                    const setStatus = (text, isError = false) => {
                        if (!status) return;
                        status.textContent = text || '';
                        status.className = `mt-1 text-xs ${isError ? 'text-rose-600 dark:text-rose-300' : 'text-gray-500 dark:text-gray-400'}`;
                    };

                    const openPanel = () => {
                        panel?.classList.remove('hidden');
                        toggle?.setAttribute('aria-expanded', 'true');
                        setTimeout(() => input?.focus(), 0);
                    };

                    const closePanel = () => {
                        panel?.classList.add('hidden');
                        toggle?.setAttribute('aria-expanded', 'false');
                        setStatus('');
                        if (input) input.value = '';
                    };

                    toggle?.addEventListener('click', () => {
                        if (panel?.classList.contains('hidden')) {
                            openPanel();
                        } else {
                            closePanel();
                        }
                    });

                    cancelBtn?.addEventListener('click', closePanel);

                    const submit = async () => {
                        const name = (input?.value || '').trim();
                        if (!name) {
                            setStatus('Escribe un nombre para el tipo.', true);
                            input?.focus();
                            return;
                        }

                        addBtn.disabled = true;
                        cancelBtn && (cancelBtn.disabled = true);
                        setStatus('Guardando…');

                        try {
                            const res = await fetch('{{ route('food.shopping-list.types.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf || '',
                                },
                                body: JSON.stringify({ name }),
                            });

                            const payload = await res.json().catch(() => null);
                            if (!res.ok || !payload?.data?.slug) {
                                const msg = payload?.message || payload?.errors?.name?.[0] || 'No se pudo crear el tipo.';
                                setStatus(msg, true);
                                return;
                            }

                            const option = document.createElement('option');
                            option.value = payload.data.slug;
                            option.textContent = payload.data.name;
                            select?.appendChild(option);
                            if (select) select.value = payload.data.slug;
                            closePanel();
                        } catch (e) {
                            setStatus('Error de red al crear el tipo.', true);
                        } finally {
                            addBtn.disabled = false;
                            cancelBtn && (cancelBtn.disabled = false);
                        }
                    };

                    addBtn?.addEventListener('click', submit);
                    input?.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            submit();
                        }
                        if (e.key === 'Escape') {
                            e.preventDefault();
                            closePanel();
                        }
                    });
                };

                fields.forEach(initField);
            });
        </script>
    @endpush

    {{-- Modal Precio --}}
    <div id="price-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick="if(event.target===this) closePriceModal()">
        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Registrar Precio</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4" id="modal-product-name"></p>
            <form id="price-form">
                <div class="space-y-3">
                    <div>
                        <label class="{{ $label }}">Precio Total Pagado</label>
                        <input type="number" step="0.01" id="modal-price" placeholder="0.00" class="{{ $input }}" required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Tienda/Vendor</label>
                        <input type="text" id="modal-vendor" placeholder="Ej: Walmart, Soriana" class="{{ $input }}">
                    </div>
                </div>
                <div class="flex gap-2 mt-6">
                    <button type="button" onclick="closePriceModal()" class="{{ $btnSecondary }} flex-1">
                        Cancelar
                    </button>
                    <button type="submit" class="{{ $btnPrimary }} flex-1">
                        ✓ Marcar Comprado
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Crear Producto --}}
    <div id="create-product-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 overflow-y-auto" onclick="if(event.target===this) closeCreateProductModal()">
        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 max-w-2xl w-full mx-4 my-8 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Crear Producto en Catálogo</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Este producto no existe en tu catálogo. Compléta los datos para crearlo:</p>

            <form id="create-product-form" class="space-y-4">
                <input type="hidden" id="create-item-id">

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="{{ $label }}">Nombre del Producto *</label>
                        <input type="text" id="create-name" required class="{{ $input }}">
                    </div>

                    <div>
                        <label class="{{ $label }}">Marca</label>
                        <input type="text" id="create-brand" class="{{ $input }}">
                    </div>

                    <div>
                        <label class="{{ $label }}">Código de Barras</label>
                        <input type="text" id="create-barcode" class="{{ $input }}">
                    </div>

                    <div>
                        <label class="{{ $label }}">Tipo</label>
                        <select id="create-type" class="{{ $input }}">
                            <option value="">Selecciona...</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="{{ $label }}">Ubicación</label>
                        <select id="create-location" class="{{ $input }}">
                            <option value="">Selecciona...</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="{{ $label }}">Unidad Base</label>
                        <select id="create-unit-base" class="{{ $input }}">
                            <option value="unit">Unidad</option>
                            <option value="g">Gramos</option>
                            <option value="ml">Mililitros</option>
                            <option value="kg">Kilogramos</option>
                            <option value="l">Litros</option>
                        </select>
                    </div>

                    <div>
                        <label class="{{ $label }}">Factor de Tamaño</label>
                        <input type="number" step="0.001" id="create-unit-size" value="1" class="{{ $input }}">
                    </div>

                    <div>
                        <label class="{{ $label }}">Stock Mínimo</label>
                        <input type="number" step="0.1" id="create-min-stock" placeholder="Ej: 3" class="{{ $input }}">
                    </div>

                    <div>
                        <label class="{{ $label }}">Vida Útil (días)</label>
                        <input type="number" id="create-shelf-life" placeholder="Ej: 7" class="{{ $input }}">
                    </div>
                </div>

                <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeCreateProductModal()" class="{{ $btnSecondary }} flex-1">
                        Cancelar
                    </button>
                    <button type="submit" class="{{ $btnPrimary }} flex-1">
                        ✓ Crear y Vincular
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const listId = {{ $list->id ?? 'null' }};
        let currentListId = listId;
        let currentItemId = null;
        let foundProducts = [];

        // Toggle item checked
        async function toggleItem(listId, itemId, isChecked) {
            try {
                const res = await fetch(`/food/shopping-list/${listId}/items/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ is_checked: isChecked }),
                });
                if (res.ok) {
                    location.reload();
                }
            } catch (err) {
                console.error(err);
            }
        }

        // Show price modal
        function showPriceModal(listId, itemId, productName, qty) {
            currentListId = listId;
            currentItemId = itemId;
            document.getElementById('modal-product-name').textContent = `${productName} (${qty} unidades)`;
            document.getElementById('price-modal').classList.remove('hidden');
            document.getElementById('modal-price').focus();
        }

        // Close price modal
        function closePriceModal() {
            document.getElementById('price-modal').classList.add('hidden');
            document.getElementById('price-form').reset();
        }

        // Show create product modal
        function showCreateProductModal(name, itemId) {
            document.getElementById('create-name').value = name;
            document.getElementById('create-item-id').value = itemId;
            document.getElementById('create-product-modal').classList.remove('hidden');
        }

        // Close create product modal
        function closeCreateProductModal() {
            document.getElementById('create-product-modal').classList.add('hidden');
            document.getElementById('create-product-form').reset();
        }

        // Submit price
        document.getElementById('price-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const price = document.getElementById('modal-price').value;
            const vendor = document.getElementById('modal-vendor').value;

            try {
                const res = await fetch(`/food/shopping-list/${currentListId}/items/${currentItemId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        is_checked: true,
                        actual_price: parseFloat(price),
                        vendor_name: vendor,
                    }),
                });
                if (res.ok) {
                    location.reload();
                } else {
                    alert('Error al guardar');
                }
            } catch (err) {
                console.error(err);
                alert('Error al guardar');
            }
        });

        // NUEVA: Autocompletar barcode
        let barcodeTimeout;
        document.getElementById('create-barcode')?.addEventListener('input', async (e) => {
            clearTimeout(barcodeTimeout);
            const code = e.target.value.trim();

            // Solo buscar si tiene al menos 8 caracteres (códigos de barras típicos)
            if (code.length < 8) {
                return;
            }

            // Crear indicador de búsqueda
            const statusSpan = document.createElement('span');
            statusSpan.id = 'barcode-status';
            statusSpan.className = 'text-xs text-blue-600 mt-1';
            statusSpan.textContent = '🔍 Buscando producto...';

            const existingStatus = document.getElementById('barcode-status');
            if (existingStatus) {
                existingStatus.remove();
            }
            e.target.parentElement.appendChild(statusSpan);

            barcodeTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`/food/barcode/${encodeURIComponent(code)}`, {
                        headers: {
                            'Authorization': `Bearer ${localStorage.getItem('api_token') || ''}`,
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        credentials: 'same-origin',
                    });

                    const data = await res.json();

                    if (data.found) {
                        // Autocompletar campos
                        const productData = data.data;

                        if (productData.name && !document.getElementById('create-name').value) {
                            document.getElementById('create-name').value = productData.name;
                        }

                        if (productData.brand) {
                            document.getElementById('create-brand').value = productData.brand;
                        }

                        if (productData.type_id) {
                            document.getElementById('create-type').value = productData.type_id;
                        }

                        if (productData.location_id) {
                            document.getElementById('create-location').value = productData.location_id;
                        }

                        if (productData.unit_base) {
                            document.getElementById('create-unit-base').value = productData.unit_base;
                        }

                        if (productData.unit_size) {
                            document.getElementById('create-unit-size').value = productData.unit_size;
                        }

                        if (productData.min_stock_qty) {
                            document.getElementById('create-min-stock').value = productData.min_stock_qty;
                        }

                        if (productData.shelf_life_days || productData.suggested_shelf_life) {
                            document.getElementById('create-shelf-life').value = productData.shelf_life_days || productData.suggested_shelf_life;
                        }

                        // Actualizar status
                        statusSpan.textContent = data.source === 'local'
                            ? '✅ Datos cargados desde tu inventario'
                            : '✅ Datos cargados desde OpenFoodFacts';
                        statusSpan.className = 'text-xs text-emerald-600 mt-1';

                        // Si es producto local, avisar que ya existe
                        if (data.source === 'local') {
                            statusSpan.textContent += ' (Este producto ya existe en tu catálogo)';
                            statusSpan.className = 'text-xs text-amber-600 mt-1';
                        }

                    } else {
                        statusSpan.textContent = '⚠️ Código no encontrado. Completa datos manualmente.';
                        statusSpan.className = 'text-xs text-amber-600 mt-1';
                    }

                } catch (err) {
                    console.error(err);
                    statusSpan.textContent = '❌ Error al buscar. Verifica tu conexión.';
                    statusSpan.className = 'text-xs text-rose-600 mt-1';
                }

                // Remover status después de 5 segundos
                setTimeout(() => {
                    statusSpan?.remove();
                }, 5000);
            }, 800); // Esperar 800ms después de que deje de escribir
        });

        // Submit create product
        document.getElementById('create-product-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const itemId = document.getElementById('create-item-id').value;
            const name = document.getElementById('create-name').value;
            const brand = document.getElementById('create-brand').value;
            const barcode = document.getElementById('create-barcode').value;
            const typeId = document.getElementById('create-type').value;
            const locationId = document.getElementById('create-location').value;
            const unitBase = document.getElementById('create-unit-base').value;
            const unitSize = document.getElementById('create-unit-size').value;
            const minStock = document.getElementById('create-min-stock').value;
            const shelfLife = document.getElementById('create-shelf-life').value;

            try {
                const res = await fetch('{{ route('food.shopping-list.items.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        list_id: listId,
                        name,
                        qty_to_buy_base: 1,
                        create_product: true,
                        brand,
                        barcode,
                        type_id: typeId || null,
                        location_id: locationId || null,
                        unit_base: unitBase,
                        unit_size: parseFloat(unitSize),
                        min_stock_qty: minStock ? parseFloat(minStock) : null,
                        shelf_life_days: shelfLife ? parseInt(shelfLife) : null,
                    }),
                });

                if (res.ok) {
                    location.reload();
                } else {
                    alert('Error al crear producto');
                }
            } catch (err) {
                console.error(err);
                alert('Error al crear producto');
            }
        });

        // Quick add (form submit)
        document.querySelector('[data-add-item-form]')?.addEventListener('submit', async (event) => {
            event.preventDefault();

            const nameInput = document.getElementById('search-product-input');
            const qtyInput = document.getElementById('quick-qty');
            const statusEl = document.getElementById('search-status');

            const name = (nameInput?.value || '').trim();
            const qty = qtyInput?.value || '1';

            if (!name) {
                if (statusEl) {
                    statusEl.textContent = 'Escribe el nombre del producto para agregarlo.';
                    statusEl.className = 'mt-2 text-xs text-amber-700 dark:text-amber-300';
                }
                nameInput?.focus();
                return;
            }

            // Si hay un producto encontrado en la búsqueda
            const found = foundProducts.find((p) => p.name.toLowerCase() === name.toLowerCase());

            if (found) {
                await addItemToList(found.id, found.name, qty);
            } else {
                showCreateProductModal(name, null);
            }
        });

        // Search products as you type
        let searchTimeout;
        document.getElementById('search-product-input')?.addEventListener('input', async (e) => {
            clearTimeout(searchTimeout);
            const term = e.target.value;
            const statusEl = document.getElementById('search-status');

            if (term.length < 2) {
                statusEl.textContent = '';
                foundProducts = [];
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`{{ route('food.shopping-list.items.search') }}?q=${encodeURIComponent(term)}`);
                    const data = await res.json();
                    foundProducts = data.data || [];

                    if (foundProducts.length > 0) {
                        const match = foundProducts[0];
                        statusEl.textContent = `Encontrado: ${match.name} (Stock: ${match.stock})`;
                        statusEl.className = 'mt-2 text-xs text-emerald-700 dark:text-emerald-300';
                    } else {
                        statusEl.textContent = `"${term}" no existe en tu catálogo. Se creará al agregar.`;
                        statusEl.className = 'mt-2 text-xs text-amber-700 dark:text-amber-300';
                    }
                } catch (err) {
                    console.error(err);
                }
            }, 300);
        });

        // Add item to list helper
        async function addItemToList(productId, name, qty) {
            try {
                const res = await fetch('{{ route('food.shopping-list.items.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        list_id: listId,
                        product_id: productId,
                        name,
                        qty_to_buy_base: parseFloat(qty),
                    }),
                });

                if (res.ok) {
                    location.reload();
                } else {
                    alert('Error al agregar');
                }
            } catch (err) {
                console.error(err);
                alert('Error al agregar');
            }
        }

        // Modo Tienda - Vista simplificada para comprar
        let storeMode = false;
        document.getElementById('toggle-store-mode')?.addEventListener('click', () => {
            storeMode = !storeMode;
            const btn = document.getElementById('toggle-store-mode');
            const sidebar = document.querySelector('[data-shopping-sidebar]');
            const addForm = document.querySelector('[data-add-item-form]');

            if (storeMode) {
                btn.textContent = 'Vista normal';
                btn.setAttribute('aria-pressed', 'true');
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-emerald-500', 'text-emerald-600', 'bg-emerald-50');

                // Ocultar sidebar y formulario de agregar
                if (sidebar) sidebar.classList.add('hidden');
                if (addForm) addForm.classList.add('hidden');

                // Hacer items más grandes y táctiles
                document.querySelectorAll('#items-container > div').forEach(item => {
                    item.classList.add('py-4');
                    const checkbox = item.querySelector('button');
                    if (checkbox) {
                        checkbox.classList.remove('h-6', 'w-6');
                        checkbox.classList.add('h-10', 'w-10');
                    }
                });

                // Expandir contenedor de items
                const mainCol = document.querySelector('[data-shopping-main]');
                if (mainCol) mainCol.classList.replace('md:col-span-2', 'md:col-span-3');
            } else {
                btn.textContent = 'Modo tienda';
                btn.setAttribute('aria-pressed', 'false');
                btn.classList.add('border-blue-500', 'text-blue-600');
                btn.classList.remove('border-emerald-500', 'text-emerald-600', 'bg-emerald-50');

                // Mostrar sidebar y formulario
                if (sidebar) sidebar.classList.remove('hidden');
                if (addForm) addForm.classList.remove('hidden');

                // Restaurar tamaño de items
                document.querySelectorAll('#items-container > div').forEach(item => {
                    item.classList.remove('py-4');
                    const checkbox = item.querySelector('button');
                    if (checkbox) {
                        checkbox.classList.add('h-6', 'w-6');
                        checkbox.classList.remove('h-10', 'w-10');
                    }
                });

                // Restaurar columnas
                const mainCol = document.querySelector('[data-shopping-main]');
                if (mainCol) mainCol.classList.replace('md:col-span-3', 'md:col-span-2');
            }
        });

        // Barcode Scanner para búsqueda de productos
        const searchInput = document.getElementById('search-product-input');
        const scanBtn = document.getElementById('scan-product-btn');

        if (searchInput && scanBtn && window.BarcodeScanner) {
            const scanner = new window.BarcodeScanner({
                targetInput: searchInput,
                onScan: (code) => {
                    console.log('Código escaneado:', code);
                }
            });

            scanBtn.addEventListener('click', () => scanner.open());
        }
    </script>
    @endpush
</x-layouts.app>

<x-quick-product-modal :locations="$locations ?? []" :types="$types ?? []" />
