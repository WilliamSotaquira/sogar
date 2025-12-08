<x-layouts.app :title="$product->name">
    @php
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
        $infoLabel = 'text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400';
        $infoValue = 'text-lg font-semibold text-gray-900 dark:text-gray-100';
    @endphp

    <div class="mx-auto w-full max-w-5xl space-y-6">
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-4 text-white">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-wide font-semibold">Detalle de producto</p>
                        <h1 class="text-3xl font-bold">{{ $product->name }}</h1>
                        <p class="text-sm text-white/80">{{ $product->brand ?: 'Sin marca' }} • Presentación {{ $presentationLabel }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('food.products.index') }}" class="{{ $btnSecondary }} bg-white/10 text-white border-white/20">Listado</a>
                        <a href="{{ route('food.products.edit', $product) }}" class="{{ $btnSecondary }} bg-white text-emerald-600 border-white">Editar</a>
                        <a href="{{ route('food.prices.show', $product) }}" class="{{ $btnSecondary }} bg-white text-emerald-600 border-white">Precios</a>
                    </div>
                </div>
                @if($product->barcode)
                    <div class="text-sm text-white/80 flex items-center gap-2">
                        <span class="font-semibold">Código:</span>
                        <span class="font-mono">{{ $product->barcode }}</span>
                        <a href="https://world.openfoodfacts.org/product/{{ $product->barcode }}" target="_blank" rel="noopener" class="underline text-white">Ver en OpenFoodFacts ↗</a>
                    </div>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 dark:border-gray-800 px-5 py-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Identificación visual</p>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Imagen y datos clave</h2>
                </div>
                <div class="p-5">
                        <dl class="grid gap-4 text-sm text-gray-700 dark:text-gray-300 md:grid-cols-2 xl:grid-cols-3">
                            <div class="md:col-span-2 xl:col-span-3">
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Código de barras</dt>
                                <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100 flex flex-wrap items-center gap-2">
                                    {{ $product->barcode ?: 'No registrado' }}
                                    @if($product->barcode)
                                        <a href="https://world.openfoodfacts.org/product/{{ $product->barcode }}" target="_blank" rel="noopener" class="text-emerald-600 underline-offset-2 hover:underline">Ver en OpenFoodFacts ↗</a>
                                    @endif
                                </dd>
                            </div>
                            <div class="md:col-span-2">
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Nombre comercial</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Marca</dt>
                                <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $product->brand ?: 'No definida' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipo</dt>
                                <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $product->type?->name ?: 'Sin tipo' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Presentación</dt>
                                <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $presentationLabel }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Ubicación por defecto</dt>
                                <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $product->defaultLocation?->name ?: 'Sin ubicación' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Estado</dt>
                                <dd class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' }}">
                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                </dd>
                            </div>
                            <div class="md:col-span-2 xl:col-span-3">
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Descripción / notas</dt>
                                <dd class="mt-1 leading-6 text-gray-700 dark:text-gray-200">
                                    {{ $product->description ?: 'Sin notas adicionales.' }}
                                </dd>
                            </div>
                        </dl>
                </div>
            </section>
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 dark:border-gray-800 px-5 py-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Imagen del producto</p>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Vista previa (máx. 250px)</h2>
                </div>
                @php $imageSrc = $product->image_url ?? $product->image_path; @endphp
                <div class="p-5">
                    <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-center p-4 min-h-[220px]">
                        @if($imageSrc)
                            <a href="{{ $imageSrc }}" target="_blank" rel="noopener" class="block cursor-zoom-in focus-visible:outline-none focus-visible:ring focus-visible:ring-emerald-400 rounded-lg" aria-label="Abrir imagen en tamaño completo">
                                <img src="{{ $imageSrc }}" alt="{{ $product->name }}" class="mx-auto max-h-[250px] max-w-[250px] w-full object-contain rounded-lg">
                            </a>
                        @else
                            <span class="text-sm text-gray-500 dark:text-gray-400 text-center">Sin imagen disponible</span>
                        @endif
                    </div>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        {{ $imageSrc ? 'Haz clic o toca la miniatura para abrir la imagen completa en otra pestaña.' : 'Puedes agregar una imagen desde la edición del producto.' }}
                    </p>
                </div>
            </section>
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
                <div class="border-b border-gray-100 dark:border-gray-800 px-5 py-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Resumen de inventario</p>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Indicadores para la gestión diaria</h2>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
                    <dl class="rounded-lg border border-gray-200 bg-white/40 p-4 dark:border-gray-700 dark:bg-gray-900/60">
                        <dt class="{{ $infoLabel }}">Stock disponible</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($currentStock ?? 0, 2) }} {{ $product->unit_base }}</dd>
                        <dd class="text-xs text-gray-500 dark:text-gray-400 mt-1">Mínimo definido: {{ $product->min_stock_qty ?? 'N/A' }}</dd>
                    </dl>
                    <dl class="rounded-lg border border-gray-200 bg-white/40 p-4 dark:border-gray-700 dark:bg-gray-900/60">
                        <dt class="{{ $infoLabel }}">Vida útil</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $product->shelf_life_days ? $product->shelf_life_days . ' días' : 'Sin definir' }}
                        </dd>
                        <dd class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $expiringSoon }} lote(s) caducan en ≤ 7 días</dd>
                    </dl>
                    <dl class="rounded-lg border border-gray-200 bg-white/40 p-4 dark:border-gray-700 dark:bg-gray-900/60">
                        <dt class="{{ $infoLabel }}">Precio más reciente</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            @if($latestPrice)
                                ${{ number_format($latestPrice->price_per_base, 2) }}
                            @else
                                —
                            @endif
                        </dd>
                        <dd class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            @if($latestPrice)
                                {{ $latestPrice->vendor ?: 'Sin vendor' }} • {{ optional($latestPrice->captured_on)->format('d M Y') }}
                            @else
                                Agrega el primer precio para comparar cambios.
                            @endif
                        </dd>
                    </dl>
                    <dl class="rounded-lg border border-gray-200 bg-white/40 p-4 dark:border-gray-700 dark:bg-gray-900/60">
                        <dt class="{{ $infoLabel }}">Porción guardada</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $product->presentation_qty ? number_format($product->presentation_qty, 2) . ' ' . $product->unit_base : 'Sin definir' }}
                        </dd>
                        <dd class="text-xs text-gray-500 dark:text-gray-400 mt-1">Utilizada para sugerencias y cálculo de compras.</dd>
                    </dl>
                    <dl class="rounded-lg border border-gray-200 bg-white/40 p-4 dark:border-gray-700 dark:bg-gray-900/60">
                        <dt class="{{ $infoLabel }}">Unidad base</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-gray-100 uppercase">{{ $product->unit_base }}</dd>
                        <dd class="text-xs text-gray-500 dark:text-gray-400 mt-1">Conversión usada en inventario y lotes.</dd>
                    </dl>
                    <dl class="rounded-lg border border-gray-200 bg-white/40 p-4 dark:border-gray-700 dark:bg-gray-900/60">
                        <dt class="{{ $infoLabel }}">Lotes registrados</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $openBatches->count() }}</dd>
                        <dd class="text-xs text-gray-500 dark:text-gray-400 mt-1">Incluye solo los lotes con inventario abierto.</dd>
                    </dl>
                </div>
            </section>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lotes activos</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Seguimiento de lo que ya tienes almacenado</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">Ubicación</th>
                            <th class="px-4 py-2 text-left font-semibold">Cantidad restante</th>
                            <th class="px-4 py-2 text-left font-semibold">Ingresó</th>
                            <th class="px-4 py-2 text-left font-semibold">Caduca</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($openBatches as $batch)
                            <tr>
                                <td class="px-4 py-3">{{ $batch->location?->name ?: 'Sin ubicación' }}</td>
                                <td class="px-4 py-3 font-semibold">{{ number_format($batch->qty_remaining_base, 2) }} {{ $batch->unit_base }}</td>
                                <td class="px-4 py-3">{{ optional($batch->entered_on)->format('d M Y') ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($batch->expires_on)
                                        <span class="{{ $batch->expires_on->isPast() ? 'text-rose-600' : 'text-gray-900 dark:text-gray-100' }}">
                                            {{ $batch->expires_on->format('d M Y') }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500">No hay lotes activos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Últimos movimientos</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Entradas y salidas recientes</p>
                </div>
            </div>
            <div class="p-6">
                @if($recentMovements->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay movimientos recientes.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($recentMovements as $movement)
                            <li class="flex items-start justify-between rounded-lg border border-gray-100 dark:border-gray-800 p-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($movement->reason ?? 'Movimiento') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $movement->note ?: 'Sin nota' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold {{ $movement->qty_delta_base >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $movement->qty_delta_base >= 0 ? '+' : '' }}{{ number_format($movement->qty_delta_base, 2) }} {{ $product->unit_base }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($movement->occurred_on)->format('d M Y') ?: $movement->created_at->format('d M Y') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
