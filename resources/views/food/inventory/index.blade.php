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
    @endphp
    <div class="mx-auto w-full max-w-7xl space-y-6">
        {{-- Header --}}
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Inventario doméstico</p>
                    <h1 class="text-3xl font-bold">Stock por Ubicación</h1>
                    <p class="text-sm text-white/80">Revisa lotes, ubicaciones y fechas de caducidad</p>
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

        {{-- Métricas de resumen --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <span class="text-2xl">📍</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $locationsWithInventory }}</p>
                        <p class="text-xs text-gray-500">Ubicaciones con inventario</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <span class="text-2xl">📦</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalItems }}</p>
                        <p class="text-xs text-gray-500">Lotes registrados</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <span class="text-2xl">⚠️</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $expiringCount }}</p>
                        <p class="text-xs text-gray-500">Por vencer (7 días)</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                        <span class="text-2xl">🚫</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $expiredCount }}</p>
                        <p class="text-xs text-gray-500">Caducados</p>
                    </div>
                </div>
            </div>
        </div>

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
                    <span id="inventory-status" class="text-xs text-gray-500 dark:text-gray-400"></span>
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
                <div id="inventory-scanner" class="mt-3 hidden rounded-xl border border-gray-200 bg-white p-3 text-center text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
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
        <div class="grid gap-5 xl:grid-cols-2">
            @forelse($locationCards as $card)
                @php
                    $topBatches = $card->batches->take(3);
                @endphp
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex flex-col gap-4">
                    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full" style="background-color: {{ $card->color ?? '#94a3b8' }};"></span>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $card->name }}</h2>
                            </div>
                            @if($card->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $card->description }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-3 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <span>Productos: <strong class="text-gray-900 dark:text-gray-100">{{ $card->unique_products }}</strong></span>
                            <span>Lotes: <strong class="text-gray-900 dark:text-gray-100">{{ $card->batches->count() }}</strong></span>
                            <span>Por vencer: <strong class="text-amber-600 dark:text-amber-400">{{ $card->expiring }}</strong></span>
                            <span>Caducados: <strong class="text-rose-600 dark:text-rose-400">{{ $card->expired }}</strong></span>
                        </div>
                    </header>

                    @if($card->batches->isEmpty())
                        <div class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 p-4 text-sm text-gray-500 dark:text-gray-400">
                            No hay productos asignados a esta ubicación todavía.
                        </div>
                    @else
                        <div class="rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-800/40 p-4">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">Últimos movimientos</p>
                            <ul class="space-y-3">
                                @foreach($topBatches as $batch)
                                    @php
                                        $expires = $batch->expires_on ? \Carbon\Carbon::parse($batch->expires_on) : null;
                                        $days = $expires ? now()->diffInDays($expires, false) : null;
                                        $expiryBadge = null;
                                        $expiryText = $expires ? $expires->format('d M Y') : 'Sin fecha';
                                        if ($days !== null) {
                                            if ($days < 0) {
                                                $expiryBadge = 'Caducado';
                                            } elseif ($days === 0) {
                                                $expiryBadge = 'Caduca hoy';
                                            } elseif ($days <= 7) {
                                                $expiryBadge = "En {$days} d";
                                            }
                                        }
                                    @endphp
                                    <li class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between inventory-row" data-product-id="{{ $batch->product_id }}">
                                        <div class="flex items-center gap-3">
                                            @if($batch->product->image_url || $batch->product->image_path)
                                                <img src="{{ $batch->product->image_url ?? $batch->product->image_path }}" alt="{{ $batch->product->name }}" class="h-10 w-10 rounded object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                                            @else
                                                <div class="h-10 w-10 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-xs text-gray-400">Sin img</div>
                                            @endif
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $batch->product->name }}</p>
                                                <p class="text-xs text-gray-500">{{ number_format($batch->qty_remaining_base, 1) }} {{ $batch->unit_base }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4 text-xs text-gray-500">
                                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $expiryText }}</span>
                                            @if($expiryBadge)
                                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $expiryBadge }}</span>
                                            @endif
                                            <a href="{{ route('food.products.show', $batch->product) }}" class="text-emerald-600 font-semibold">Ver</a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Total almacenado: <strong class="text-gray-900 dark:text-gray-100">{{ number_format($card->total_qty, 1) }}</strong> unidades base</span>
                            @if($card->id)
                                <a href="{{ route('food.locations.show', $card->id) }}" class="text-emerald-600 font-semibold">Ver detalle de la ubicación →</a>
                            @endif
                        </div>
                    @endif
                </section>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-8 text-center text-gray-500 dark:text-gray-400">
                    @if($activeLocation || $activeType)
                        <p>No hay resultados que coincidan con los filtros aplicados.</p>
                        <a href="{{ route('food.inventory.index') }}" class="mt-3 inline-flex items-center justify-center rounded-full border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:border-gray-700 dark:text-gray-100">Limpiar filtros</a>
                    @else
                        No hay ubicaciones registradas todavía. Crea una ubicación para comenzar a asociar tus productos.
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Alertas de caducidad --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Alertas de caducidad</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Monitorea los lotes que requieren acción inmediata.</p>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $expiringSoonList->count() }} alerta{{ $expiringSoonList->count() === 1 ? '' : 's' }}</p>
            </div>
            <div class="overflow-x-auto">
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
                        @forelse($expiringSoonList as $batch)
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
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No tienes lotes próximos a caducar.
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
