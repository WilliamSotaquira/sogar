<x-layouts.app :title="__('Inventario de Alimentos')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
        $buildQuery = fn ($loc, $type) => array_filter([
            'location_id' => $loc,
            'type_id' => $type,
        ], fn ($value) => $value !== null && $value !== '');

        $isExpiringSoon = function($batch) {
            if (!$batch->expires_on) return false;
            $days = now()->diffInDays(\Carbon\Carbon::parse($batch->expires_on), false);
            return $days >= 0 && $days <= 7;
        };

        $isExpired = function($batch) {
            if (!$batch->expires_on) return false;
            return now()->gt(\Carbon\Carbon::parse($batch->expires_on));
        };

        $totalItems = $batches->count();
        $expiringCount = $batches->filter($isExpiringSoon)->count();
        $expiredCount = $batches->filter($isExpired)->count();

        $groupedByLocation = $batches->groupBy(fn($batch) => $batch->location_id ?: 'unassigned');

        $locationCards = $locations->map(function($location) use ($groupedByLocation, $isExpiringSoon, $isExpired) {
            $batchesForLocation = $groupedByLocation->get($location->id, collect())->sortBy(function($batch) {
                return $batch->expires_on ? \Carbon\Carbon::parse($batch->expires_on) : now()->addYears(5);
            });

            return (object) [
                'id' => $location->id,
                'name' => $location->name,
                'color' => $location->color,
                'description' => $location->description,
                'batches' => $batchesForLocation,
                'total_qty' => $batchesForLocation->sum('qty_remaining_base'),
                'unique_products' => $batchesForLocation->pluck('product_id')->unique()->count(),
                'expiring' => $batchesForLocation->filter($isExpiringSoon)->count(),
                'expired' => $batchesForLocation->filter($isExpired)->count(),
            ];
        });

        if ($groupedByLocation->has('unassigned') && !$activeLocation) {
            $unassignedBatches = $groupedByLocation->get('unassigned');
            $locationCards->push((object) [
                'id' => null,
                'name' => 'Sin ubicación asignada',
                'color' => '#94a3b8',
                'description' => 'Productos que aún no tienen un espacio definido.',
                'batches' => $unassignedBatches,
                'total_qty' => $unassignedBatches->sum('qty_remaining_base'),
                'unique_products' => $unassignedBatches->pluck('product_id')->unique()->count(),
                'expiring' => $unassignedBatches->filter($isExpiringSoon)->count(),
                'expired' => $unassignedBatches->filter($isExpired)->count(),
            ]);
        }

        $locationCards = $locationCards->sortBy(fn($card) => $card->name)->values();
        if ($activeLocation) {
            $locationCards = $locationCards->filter(fn ($card) => $card->id === $activeLocation->id)->values();
        }
        $locationsWithInventory = $locationCards->filter(fn($card) => $card->batches->isNotEmpty())->count();
        $expiringSoonList = $batches->filter($isExpiringSoon)->sortBy('expires_on')->take(6);
        $pendingInventoryCount = $pendingInventoryCount ?? 0;
        $pendingInventoryItems = $pendingInventoryItems ?? collect();
        $pendingInventoryFilterOptions = $pendingInventoryFilterOptions ?? collect();
        $activePendingListId = $activePendingListId ?? null;
        $persistedInventoryFilters = collect(request()->only(['location_id', 'type_id']))
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    @endphp
    <div class="mx-auto w-full max-w-7xl space-y-6 pb-28 md:pb-0">
        {{-- Header --}}
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-8 shadow-lg dark:from-emerald-600 dark:to-teal-700">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Inventario doméstico</p>
                    <h1 class="text-3xl font-bold">Stock por Ubicación</h1>
                    <p class="text-sm text-white/80">Revisa lotes, ubicaciones y fechas de caducidad</p>
                    @if($pendingInventoryCount > 0)
                        <span class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">
                            ⚠️ Pendientes inventario: {{ $pendingInventoryCount }}
                        </span>
                    @endif
                </div>
                <div class="flex gap-2 flex-wrap">
                    @if($activeLocation)
                        <a href="{{ route('food.locations.show', $activeLocation) }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm transition">
                            📍 Ver {{ $activeLocation->name }}
                        </a>
                        <a href="{{ route('food.locations.edit', $activeLocation) }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm transition">
                            ✏️ Editar ubicación
                        </a>
                    @else
                        <a href="{{ route('food.locations.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm transition">
                            📍 Ubicaciones
                        </a>
                        <a href="{{ route('food.products.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm transition">
                            📦 Productos
                        </a>
                    @endif
                    <a href="{{ route('food.purchases.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm transition">
                        🛒 Compras
                    </a>
                </div>
            </div>
        </div>

        @if($pendingInventoryCount > 0)
            <section id="pending-inventory-panel" class="rounded-lg border border-amber-200 bg-amber-50/70 p-5 shadow-sm dark:border-amber-900/40 dark:bg-amber-900/20">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-200">Alertas de sincronización</p>
                        <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-100">{{ $pendingInventoryCount }} ítem{{ $pendingInventoryCount === 1 ? '' : 's' }} siguen sin lote</h2>
                        <p class="text-sm text-amber-800/80 dark:text-amber-100/80">Identifica los productos marcados como comprados que aún no generan stock.</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                        @if($pendingInventoryFilterOptions->isNotEmpty())
                            <form method="GET" action="{{ route('food.inventory.index') }}" class="min-w-[220px]">
                                @foreach($persistedInventoryFilters as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <label class="{{ $label }}">Filtrar por lista</label>
                                <select name="pending_list_id" class="{{ $input }}" onchange="this.form.submit()">
                                    <option value="">Todas las listas con pendientes</option>
                                    @foreach($pendingInventoryFilterOptions as $listOption)
                                        <option value="{{ $listOption->id }}" @selected((int) request('pending_list_id') === $listOption->id)>{{ $listOption->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                        <div class="flex flex-wrap gap-2 text-xs font-semibold text-amber-800 dark:text-amber-100">
                            <a href="{{ route('food.purchases.index') }}" class="inline-flex items-center gap-1 rounded-full border border-amber-200 px-3 py-1 hover:bg-amber-100 dark:border-amber-900/40 dark:hover:bg-amber-900/40">🛒 Registrar compra</a>
                            <a href="{{ route('food.shopping-list.index') }}" class="inline-flex items-center gap-1 rounded-full border border-amber-200 px-3 py-1 hover:bg-amber-100 dark:border-amber-900/40 dark:hover:bg-amber-900/40">🗒️ Revisar listas</a>
                        </div>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($pendingInventoryItems as $pendingItem)
                        @php
                            $checkedAgo = optional($pendingItem->checked_at)->diffForHumans();
                            $pendingReason = empty($pendingItem->product_id) ? 'Producto sin asociar' : 'Lote no creado';
                            $unitLabel = $pendingItem->unit_base ?? $pendingItem->qty_unit_label ?? 'unidad';
                        @endphp
                        <article class="rounded-lg border border-amber-100 bg-white/80 p-4 shadow-sm dark:border-amber-900/50 dark:bg-amber-950/10">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ $pendingItem->name }}</p>
                                    <p class="text-xs text-amber-800/80 dark:text-amber-100/80">Lista: {{ $pendingItem->list?->name ?? 'Lista desconocida' }} · Marcado {{ $checkedAgo ?? 'sin fecha' }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-[11px] font-semibold text-amber-900 dark:bg-amber-900/40 dark:text-amber-100">
                                    ⚠️ {{ $pendingReason }}
                                </span>
                            </div>
                            <dl class="mt-3 grid gap-3 text-xs text-amber-800/80 dark:text-amber-100/80 sm:grid-cols-3">
                                <div>
                                    <dt class="font-semibold uppercase tracking-wide">Cantidad estimada</dt>
                                    <dd class="text-amber-900 dark:text-amber-100">{{ number_format((float) ($pendingItem->qty_to_buy_base ?? 0), 2) }} {{ $unitLabel }}</dd>
                                </div>
                                <div>
                                    <dt class="font-semibold uppercase tracking-wide">Producto vinculado</dt>
                                    <dd class="text-amber-900 dark:text-amber-100">{{ $pendingItem->product?->name ?? 'Sin producto asociado' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-semibold uppercase tracking-wide">Ubicación sugerida</dt>
                                    <dd class="text-amber-900 dark:text-amber-100">{{ $pendingItem->location?->name ?? $pendingItem->product?->defaultLocation?->name ?? 'Sin ubicación' }}</dd>
                                </div>
                            </dl>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold text-amber-800 dark:text-amber-100">
                                <a href="{{ route('food.purchases.index', ['list_id' => $pendingItem->shopping_list_id]) }}" class="inline-flex items-center gap-1 rounded-full border border-amber-200 px-3 py-1 hover:bg-amber-100 dark:border-amber-900/40 dark:hover:bg-amber-900/30">🧾 Reabrir compra</a>
                                <a href="{{ route('food.shopping-list.index', ['list_id' => $pendingItem->shopping_list_id]) }}" class="inline-flex items-center gap-1 rounded-full border border-amber-200 px-3 py-1 hover:bg-amber-100 dark:border-amber-900/40 dark:hover:bg-amber-900/30">🗒️ Ver lista</a>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-amber-200 bg-white/70 px-4 py-6 text-sm text-amber-800 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-100">
                            No hay pendientes para la lista filtrada.
                            <a href="{{ route('food.inventory.index', $persistedInventoryFilters) }}" class="font-semibold text-amber-900 underline dark:text-amber-100">Ver todas las listas</a>
                        </div>
                    @endforelse
                </div>

                @if($activePendingListId)
                    <p class="mt-3 text-xs text-amber-800/80 dark:text-amber-100/80">
                        Mostrando solo la lista seleccionada.
                        <a href="{{ route('food.inventory.index', $persistedInventoryFilters) }}" class="font-semibold underline">Ver todas las listas con pendientes</a>
                    </p>
                @endif
            </section>
        @endif

        @if($activeLocation || $activeType)
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-200">Filtros activos:</span>
                    @if($activeLocation)
                        <a href="{{ route('food.inventory.index', $buildQuery(null, request('type_id'))) }}" class="inline-flex items-center gap-1 rounded-full bg-white/70 px-3 py-1 text-xs font-semibold text-emerald-700 shadow-sm dark:bg-emerald-900/40 dark:text-emerald-100">
                            📍 {{ $activeLocation->name }}
                            <span aria-hidden="true">×</span>
                        </a>
                    @endif
                    @if($activeType)
                        <a href="{{ route('food.inventory.index', $buildQuery(request('location_id'), null)) }}" class="inline-flex items-center gap-1 rounded-full bg-white/70 px-3 py-1 text-xs font-semibold text-emerald-700 shadow-sm dark:bg-emerald-900/40 dark:text-emerald-100">
                            🏷️ {{ $activeType->name }}
                            <span aria-hidden="true">×</span>
                        </a>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="text-xs text-emerald-700 dark:text-emerald-200">Mostrando resultados específicos.</span>
                    <a href="{{ route('food.inventory.index') }}" class="inline-flex items-center text-xs font-semibold text-emerald-700 hover:underline dark:text-emerald-200">Limpiar todo</a>
                </div>
            </div>
        @endif

        {{-- Acciones rápidas y búsqueda --}}
        <div class="grid gap-4 lg:grid-cols-[2fr_3fr]">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50 mb-3">⚡ Acciones rápidas</h3>
                <div class="grid gap-3 sm:grid-cols-2">
                    @if($activeLocation)
                        <a href="{{ route('food.locations.show', $activeLocation) }}" class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 text-sm font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-100">
                            <span class="text-lg">📍</span>
                            <span>Ver {{ $activeLocation->name }}</span>
                        </a>
                        <a href="{{ route('food.locations.edit', $activeLocation) }}" class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 text-sm font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-100">
                            <span class="text-lg">✏️</span>
                            <span>Editar ubicación</span>
                        </a>
                    @else
                        <a href="{{ route('food.locations.index') }}" class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 text-sm font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-100">
                            <span class="text-lg">📍</span>
                            <span>Administrar ubicaciones</span>
                        </a>
                        <a href="{{ route('food.locations.create') }}" class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 text-sm font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-100">
                            <span class="text-lg">➕</span>
                            <span>Crear nueva ubicación</span>
                        </a>
                    @endif
                    <a href="{{ route('food.purchases.index') }}" class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 text-sm font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-100">
                        <span class="text-lg">🛒</span>
                        <span>Registrar compra</span>
                    </a>
                    <a href="{{ route('food.products.index') }}" class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 text-sm font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-100">
                        <span class="text-lg">🏷️</span>
                        <span>Gestionar productos</span>
                    </a>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50">🔍 Buscar producto en stock</h3>
                    <span id="inventory-status" role="status" aria-live="polite" aria-atomic="true" class="text-xs text-gray-500 dark:text-gray-400"></span>
                </div>
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[250px]">
                        <label class="{{ $label }}">Código de barras</label>
                        <input id="inventory-barcode-input" class="{{ $input }}" placeholder="Escanea o escribe el código" />
                    </div>
                    <button type="button" id="inventory-scan" class="{{ $btnSecondary }}">
                        📷 Escanear
                    </button>
                    <button type="button" id="inventory-search" class="{{ $btnPrimary }}">
                        🔎 Buscar
                    </button>
                </div>
                <div id="inventory-scanner" role="region" aria-live="polite" aria-label="Escáner de códigos" class="mt-3 hidden rounded-xl border border-gray-200 bg-white p-3 text-center text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <div id="inventory-camera" class="overflow-hidden rounded-lg"></div>
                    <p class="mt-2">Apunta la cámara al código de barras. Se cerrará automáticamente al detectarlo.</p>
                    <button type="button" id="inventory-close" class="mt-2 text-rose-500 hover:text-rose-600 font-semibold">✕ Cerrar</button>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50">⚙️ Filtros</h3>
                @if(request()->has('location_id') || request()->has('type_id'))
                    <a href="{{ route('food.inventory.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Limpiar filtros</a>
                @endif
            </div>
            <form method="GET" action="{{ route('food.inventory.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="{{ $label }}">Ubicación</label>
                    <select name="location_id" class="{{ $input }}" onchange="this.form.submit()">
                        <option value="">Todas las ubicaciones</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" @selected(request('location_id') == $loc->id)>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="{{ $label }}">Tipo de producto</label>
                    <select name="type_id" class="{{ $input }}" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected(request('type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            @if($filterHistory->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span class="font-semibold uppercase tracking-wide">Filtros rápidos:</span>
                    @foreach($filterHistory as $entry)
                        @php
                            $query = $buildQuery($entry['location_id'] ?? null, $entry['type_id'] ?? null);
                        @endphp
                        <a href="{{ route('food.inventory.index', $query) }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-100">
                            {{ $entry['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Inventario agrupado por ubicación --}}
        <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-2 border-b border-gray-100 px-6 py-4 dark:border-gray-800/70 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Inventario por ubicación</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $locationsWithInventory }} ubicación{{ $locationsWithInventory === 1 ? '' : 'es' }} con stock · {{ $totalItems }} lote{{ $totalItems === 1 ? '' : 's' }}</p>
                </div>
                @if(!$activeLocation)
                    <div class="flex flex-wrap gap-2 text-xs">
                        <a href="{{ route('food.locations.index') }}" class="inline-flex items-center gap-1 rounded-full border border-gray-200 px-3 py-1 font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-200">Administrar ubicaciones</a>
                        <a href="{{ route('food.locations.create') }}" class="inline-flex items-center gap-1 rounded-full border border-gray-200 px-3 py-1 font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-200">Nueva ubicación</a>
                    </div>
                @endif
            </div>

            @if($locationCards->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    @if($activeLocation || $activeType)
                        <p>No hay resultados que coincidan con los filtros aplicados.</p>
                        <a href="{{ route('food.inventory.index') }}" class="mt-3 inline-flex items-center justify-center rounded-full border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-100">Limpiar filtros</a>
                    @else
                        No hay ubicaciones registradas todavía. Crea una para empezar a controlar tu inventario.
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-900/40 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="py-3 pl-6 pr-3 text-left">Ubicación</th>
                                <th scope="col" class="px-3 py-3 text-center">Productos</th>
                                <th scope="col" class="px-3 py-3 text-center">Lotes</th>
                                <th scope="col" class="px-3 py-3 text-center">Por vencer</th>
                                <th scope="col" class="px-3 py-3 text-center">Caducados</th>
                                <th scope="col" class="px-3 py-3">Último movimiento</th>
                                <th scope="col" class="px-3 py-3 text-right">Total base</th>
                                <th scope="col" class="py-3 pr-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/70">
                            @foreach($locationCards as $card)
                                @php
                                    $latestBatch = $card->batches->first();
                                    $expires = $latestBatch?->expires_on ? \Carbon\Carbon::parse($latestBatch->expires_on) : null;
                                    $latestExpiry = $expires ? $expires->format('d M Y') : 'Sin fecha';
                                    $latestQty = $latestBatch ? number_format($latestBatch->qty_remaining_base, 1) . ' ' . $latestBatch->unit_base : null;
                                @endphp
                                <tr class="hover:bg-emerald-50/40 dark:hover:bg-emerald-900/10">
                                    <td class="py-4 pl-6 pr-3 align-top">
                                        <div class="flex flex-col gap-1 text-gray-900 dark:text-gray-100">
                                            <div class="flex items-center gap-2">
                                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $card->color ?? '#94a3b8' }};"></span>
                                                <span class="font-semibold">{{ $card->name }}</span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $card->description ?? 'Sin descripción' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-center font-semibold text-gray-900 dark:text-gray-100">{{ $card->unique_products }}</td>
                                    <td class="px-3 py-4 text-center font-semibold text-gray-900 dark:text-gray-100">{{ $card->batches->count() }}</td>
                                    <td class="px-3 py-4 text-center text-amber-600 dark:text-amber-300 font-semibold">{{ $card->expiring }}</td>
                                    <td class="px-3 py-4 text-center text-rose-600 dark:text-rose-300 font-semibold">{{ $card->expired }}</td>
                                    <td class="px-3 py-4 align-top">
                                        @if($latestBatch)
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $latestBatch->product?->name ?? 'Producto eliminado' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $latestQty ?? '—' }} · {{ $latestExpiry }}</p>
                                            @if($latestBatch->product)
                                                <a href="{{ route('food.products.show', $latestBatch->product) }}" class="text-xs font-semibold text-emerald-600 hover:underline">Ver producto</a>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-400">Sin movimientos</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-right font-semibold text-gray-900 dark:text-gray-100">{{ number_format($card->total_qty, 1) }}</td>
                                    <td class="py-4 pr-6 text-right text-xs font-semibold">
                                        <div class="flex flex-col items-end gap-1 text-emerald-600">
                                            @if($card->id)
                                                <a href="{{ route('food.locations.show', $card->id) }}" class="hover:underline">Ver ubicación</a>
                                                <a href="{{ route('food.inventory.index', ['location_id' => $card->id]) }}" class="hover:underline">Filtrar inventario</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- Alertas de caducidad --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Alertas de caducidad</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Monitorea los lotes que requieren acción inmediata.</p>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $expiringSoonList->count() }} alerta{{ $expiringSoonList->count() === 1 ? '' : 's' }}</p>
            </div>

            @if($expiringSoonList->isEmpty())
                <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                    No tienes lotes próximos a caducar.
                </div>
            @else
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800/60 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold">Producto</th>
                                <th class="px-4 py-2 text-left font-semibold">Ubicación</th>
                                <th class="px-4 py-2 text-left font-semibold">Cantidad</th>
                                <th class="px-4 py-2 text-left font-semibold">Caduca</th>
                                <th class="px-4 py-2 text-left font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($expiringSoonList as $batch)
                                @php
                                    $expires = $batch->expires_on ? \Carbon\Carbon::parse($batch->expires_on) : null;
                                    $days = $expires ? now()->diffInDays($expires, false) : null;
                                @endphp
                                <tr class="hover:bg-amber-50/60 dark:hover:bg-amber-900/20">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $batch->product?->name ?? 'Producto eliminado' }}</p>
                                        @if($batch->product?->brand)
                                            <p class="text-xs text-gray-500">{{ $batch->product->brand }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                        {{ $batch->location?->name ?? 'Sin ubicación' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100 font-semibold">
                                        {{ number_format($batch->qty_remaining_base, 1) }} {{ $batch->unit_base }}
                                    </td>
                                    <td class="px-4 py-3 text-rose-600 dark:text-rose-400 font-semibold">
                                        {{ $expires ? $expires->format('d M Y') : 'Sin fecha' }}
                                        @if($days !== null)
                                            <span class="ml-2 text-xs text-gray-500">({{ $days }} día{{ $days === 1 ? '' : 's' }})</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('food.products.show', $batch->product) }}" class="text-emerald-600 text-xs font-semibold hover:underline">Ver producto</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden divide-y divide-gray-100 border-t border-gray-100 dark:divide-gray-800 dark:border-gray-800">
                    @foreach($expiringSoonList as $batch)
                        @php
                            $expires = $batch->expires_on ? \Carbon\Carbon::parse($batch->expires_on) : null;
                            $days = $expires ? now()->diffInDays($expires, false) : null;
                        @endphp
                        <article class="flex flex-col gap-3 px-4 py-4">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $batch->product?->name ?? 'Producto eliminado' }}</p>
                                @if($batch->product?->brand)
                                    <p class="text-xs text-gray-500">{{ $batch->product->brand }}</p>
                                @endif
                            </div>
                            <dl class="grid gap-2 text-xs text-gray-600 dark:text-gray-400">
                                <div class="flex justify-between">
                                    <dt class="font-semibold uppercase tracking-wide">Ubicación</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ $batch->location?->name ?? 'Sin ubicación' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold uppercase tracking-wide">Cantidad</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ number_format($batch->qty_remaining_base, 1) }} {{ $batch->unit_base }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold uppercase tracking-wide">Caducidad</dt>
                                    <dd class="text-rose-600 dark:text-rose-400 font-semibold">{{ $expires ? $expires->format('d M Y') : 'Sin fecha' }}</dd>
                                </div>
                                @if($days !== null)
                                    <div class="flex justify-between text-[11px]">
                                        <dt class="font-semibold uppercase tracking-wide">Tiempo</dt>
                                        <dd>{{ $days }} día{{ $days === 1 ? '' : 's' }}</dd>
                                    </div>
                                @endif
                            </dl>
                            <div>
                                <a href="{{ route('food.products.show', $batch->product) }}" class="text-sm font-semibold text-emerald-600 hover:underline">Ver producto</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="fixed inset-x-0 bottom-0 z-30 mx-auto max-w-7xl px-4 pb-4 md:hidden">
        <div class="flex items-center justify-between rounded-lg bg-white/95 p-3 shadow-2xl ring-1 ring-gray-200 dark:bg-gray-900/95 dark:ring-gray-700">
            <a href="{{ route('food.shopping-list.index') }}" class="flex-1 text-center text-xs font-semibold text-gray-600 hover:text-emerald-600">
                🗒️ Lista
            </a>
            <a href="{{ route('food.purchases.index') }}" class="flex-1 text-center text-xs font-semibold text-gray-600 hover:text-emerald-600">
                🛒 Compras
            </a>
            <a href="{{ route('food.inventory.index') }}" class="flex-1 text-center text-xs font-semibold text-emerald-600">
                📦 Inventario
            </a>
        </div>
    </div>
</x-layouts.app>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('inventory-barcode-input');
        const scanBtn = document.getElementById('inventory-scan');
        const searchBtn = document.getElementById('inventory-search');
        const statusEl = document.getElementById('inventory-status');
        const scannerWrapper = document.getElementById('inventory-scanner');
        const cameraEl = document.getElementById('inventory-camera');
        const closeBtn = document.getElementById('inventory-close');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let stream = null;
        let rafId = null;
        let detector = null;

        const setStatus = (msg, tone = 'text-amber-600') => {
            if (!statusEl) return;
            statusEl.textContent = msg || '';
            statusEl.className = 'text-xs ' + tone;
        };

        const stopScanner = async () => {
            if (rafId) cancelAnimationFrame(rafId);
            rafId = null;
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
            }
            detector = null;
            cameraEl.innerHTML = '';
            scannerWrapper?.classList.add('hidden');
            setStatus('');
        };

        const highlightProduct = (productId) => {
            document.querySelectorAll('.inventory-row').forEach(row => row.classList.remove('bg-emerald-50', 'dark:bg-emerald-900/20', 'ring-2', 'ring-emerald-500'));
            const rows = document.querySelectorAll(`.inventory-row[data-product-id="${productId}"]`);
            if (rows.length > 0) {
                rows.forEach(row => {
                    row.classList.add('bg-emerald-50', 'dark:bg-emerald-900/20', 'ring-2', 'ring-emerald-500');
                });
                rows[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                setStatus(`Encontrado: ${rows.length} ${rows.length === 1 ? 'lote' : 'lotes'}`, 'text-emerald-600');
            } else {
                setStatus('No hay stock para este código en el listado actual.', 'text-amber-600');
            }
        };

        const lookupAndHighlight = async (code) => {
            if (!code) return;
            setStatus('Buscando producto...');
            try {
                const res = await fetch('/food/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf || '',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ code }),
                });
                if (res.ok) {
                    const data = await res.json();
                    highlightProduct(data.product.id);
                } else {
                    setStatus('No se encontró el producto. ¿Lo quieres crear primero?', 'text-amber-600');
                }
            } catch (err) {
                console.warn(err);
                setStatus('Error al buscar el código.', 'text-rose-500');
            }
        };

        const startScanner = async () => {
            if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                setStatus('La cámara requiere HTTPS o localhost.', 'text-rose-500');
                return;
            }
            scannerWrapper?.classList.remove('hidden');
            setStatus('Buscando cámaras...');
            try {
                if (typeof window.ensureBarcodeDetector === 'function') {
                    try {
                        await window.ensureBarcodeDetector();
                    } catch (polyfillErr) {
                        console.warn(polyfillErr);
                    }
                }

                if (!('BarcodeDetector' in window)) {
                    setStatus('Tu navegador no soporta BarcodeDetector. Usa entrada manual.', 'text-rose-500');
                    return;
                }
                detector = new BarcodeDetector({ formats: ['ean_13', 'code_128', 'ean_8', 'qr_code'] });
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
                const video = document.createElement('video');
                video.setAttribute('playsinline', true);
                video.muted = true;
                video.srcObject = stream;
                await video.play();
                cameraEl.innerHTML = '';
                cameraEl.appendChild(video);
                setStatus('Escaneando... apunta al código.');

                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                const scan = async () => {
                    if (!video.videoWidth) {
                        rafId = requestAnimationFrame(scan);
                        return;
                    }
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    try {
                        const codes = await detector.detect(canvas);
                        if (codes.length) {
                            const text = codes[0].rawValue || '';
                            if (text) {
                                input.value = text;
                                setStatus('Código detectado: ' + text, 'text-emerald-600');
                                await stopScanner();
                                lookupAndHighlight(text);
                                return;
                            }
                        }
                    } catch (_) {}
                    rafId = requestAnimationFrame(scan);
                };
                rafId = requestAnimationFrame(scan);
            } catch (err) {
                console.warn(err);
                setStatus('No se pudo acceder a la cámara. Revisa permisos.', 'text-rose-500');
            }
        };

        scanBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            startScanner();
        });

        closeBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            stopScanner();
        });

        searchBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            lookupAndHighlight(input?.value);
        });

        input?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                lookupAndHighlight(e.target.value);
            }
        });
    });
</script>
