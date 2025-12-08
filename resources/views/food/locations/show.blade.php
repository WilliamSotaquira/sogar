<x-layouts.app :title="$location->name">
    @php
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-1';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $statsCards = [
            ['label' => 'Productos asignados', 'value' => $stats['products'], 'icon' => '🏷️'],
            ['label' => 'Lotes registrados', 'value' => $stats['batches'], 'icon' => '📦'],
            ['label' => 'Por vencer (≤7 días)', 'value' => $stats['expiring'], 'icon' => '⚠️'],
            ['label' => 'Caducados', 'value' => $stats['expired'], 'icon' => '🚫'],
        ];
        $uniqueProducts = $batches->pluck('product')->filter()->unique('id');
    @endphp

    <div class="mx-auto w-full max-w-5xl space-y-6">
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-3 text-white sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Ubicación del inventario</p>
                    <h1 class="text-3xl font-bold flex items-center gap-2">
                        <span>{{ $location->name }}</span>
                        <span class="inline-flex h-3 w-3 rounded-full" style="background-color: {{ $location->color ?? '#94a3b8' }};"></span>
                    </h1>
                    <p class="text-sm text-white/80">Slug: {{ $location->slug }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('food.locations.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                        ← Lista de ubicaciones
                    </a>
                    <a href="{{ route('food.locations.edit', $location) }}" class="{{ $btnPrimary }}">Editar ubicación</a>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Color y orden</p>
                <div class="mt-2 flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 rounded-full border border-gray-200 dark:border-gray-700" style="background-color: {{ $location->color ?? '#94a3b8' }};"></span>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Código</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $location->color ?? 'No definido' }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Orden de visualización</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $location->sort_order }}</p>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Opciones</p>
                <div class="mt-2">
                    @if($location->is_default)
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Ubicación predeterminada</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">Ubicación secundaria</span>
                    @endif
                </div>
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Tu inventario usará esta ubicación por defecto cuando así lo configures en los productos.</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($statsCards as $card)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ $card['icon'] }}</span>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $card['value'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Productos asociados</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Todos los artículos cuyo stock principal se guarda aquí.</p>
                </div>
            </div>
            <div class="p-6">
                @if($uniqueProducts->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay productos con lotes activos en esta ubicación.</p>
                @else
                    <ul class="grid gap-3 sm:grid-cols-2">
                        @foreach($uniqueProducts as $product)
                            <li class="rounded-lg border border-gray-100 dark:border-gray-800 p-3 flex items-center justify-between text-sm">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</p>
                                    @if($product->brand)
                                        <p class="text-xs text-gray-500">{{ $product->brand }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('food.products.show', $product) }}" class="text-emerald-600 text-xs font-semibold hover:underline">Ver producto</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lotes en esta ubicación</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Detalle de movimientos y fechas de caducidad.</p>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $batches->count() }} registro{{ $batches->count() === 1 ? '' : 's' }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">Producto</th>
                            <th class="px-4 py-2 text-left font-semibold">Cantidad</th>
                            <th class="px-4 py-2 text-left font-semibold">Ingresó</th>
                            <th class="px-4 py-2 text-left font-semibold">Caduca</th>
                            <th class="px-4 py-2 text-left font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($batches as $batch)
                            @php
                                $expires = $batch->expires_on;
                                $days = $expires ? now()->diffInDays($expires, false) : null;
                                $expiryClass = 'text-gray-900 dark:text-gray-100';
                                if ($days !== null && $days <= 7) {
                                    $expiryClass = 'text-rose-600 dark:text-rose-400 font-semibold';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $batch->product?->name ?? 'Producto eliminado' }}</p>
                                    @if($batch->product?->brand)
                                        <p class="text-xs text-gray-500">{{ $batch->product->brand }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($batch->qty_remaining_base, 1) }} {{ $batch->unit_base }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    {{ optional($batch->entered_on)->format('d M Y') ?: '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <p class="{{ $expiryClass }}">
                                        {{ $expires ? $expires->format('d M Y') : 'Sin fecha' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    @if($batch->status === 'ok')
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">✓ OK</span>
                                    @elseif($batch->status === 'expired')
                                        <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">🚫 Caducado</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ ucfirst($batch->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No existen lotes para esta ubicación.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
