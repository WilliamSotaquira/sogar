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
    @endphp

    <div class="mx-auto w-full max-w-5xl space-y-6">
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-8 shadow-lg dark:from-emerald-600 dark:to-teal-700">
            <div class="flex flex-col gap-3 text-white sm:flex-row sm:items-center sm:justify-between">
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

        <div x-data="{ showSelector: false, showCreator: false }" class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Productos asociados</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Todos los artículos cuyo stock principal se guarda aquí.</p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="showSelector = true"
                            class="inline-flex items-center gap-2 rounded-lg border border-emerald-600 bg-white px-4 py-2 text-sm font-semibold text-emerald-600 transition hover:bg-emerald-50 dark:border-emerald-500 dark:bg-neutral-800 dark:text-emerald-400 dark:hover:bg-emerald-900/20">
                            <span class="text-base">🔗</span>
                            <span>Asociar existente</span>
                        </button>
                        <button
                            @click="showCreator = true"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            <span class="text-base">➕</span>
                            <span>Crear producto</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal: Asociar producto existente --}}
            <div
                x-show="showSelector"
                x-cloak
                @keydown.escape.window="showSelector = false"
                @close-modal.window="showSelector = false"
                @product-assigned.window="showSelector = false; window.location.reload()"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                <div
                    @click.stop
                    class="w-full max-w-lg rounded-xl border border-blue-200 bg-white p-6 shadow-2xl dark:border-blue-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Asociar producto existente</h3>
                        <button
                            @click="showSelector = false"
                            type="button"
                            class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <livewire:food.product-assigner :location="$location" />
                </div>
            </div>

            {{-- Modal: Crear nuevo producto --}}
            <div
                x-show="showCreator"
                x-cloak
                @keydown.escape.window="showCreator = false"
                @close-modal.window="showCreator = false"
                @product-created.window="showCreator = false; window.location.reload()"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                <div
                    @click.stop
                    class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl border border-emerald-200 bg-white p-6 shadow-2xl dark:border-emerald-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Crear nuevo producto</h3>
                        <button
                            @click="showCreator = false"
                            type="button"
                            class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <livewire:food.product-creator :location="$location" />
                </div>
            </div>
            <div
                x-show="showSelector"
                x-cloak
                @keydown.escape.window="showSelector = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                <div
                    @click.stop
                    class="w-full max-w-lg rounded-xl border border-blue-200 bg-white p-6 shadow-2xl dark:border-blue-800 dark:bg-gray-900"
                    x-data="productAssigner">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Asociar producto existente</h3>
                        <button
                            @click="showSelector = false"
                            type="button"
                            class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <form @submit.prevent="assignProduct" class="space-y-4">
                                <div>
                                    <label for="product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecciona un producto existente</label>
                                    <select
                                        id="product_id"
                                        x-model="selectedProduct"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                        required>
                                        <option value="">-- Elige un producto --</option>
                                        @foreach($allProducts as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->name }}{{ $prod->brand ? ' - ' . $prod->brand : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        type="submit"
                                        :disabled="loading || !selectedProduct"
                                        :class="loading ? 'opacity-50 cursor-not-allowed' : ''"
                                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                                        <span x-show="loading">⏳</span>
                                        <span x-text="loading ? 'Asociando...' : 'Asociar a esta ubicación'"></span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="showSelector = false; selectedProduct = ''"
                                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                        Cancelar
                                    </button>
                                </div>
                                <p x-show="message" :class="messageType === 'success' ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'" class="text-sm font-medium" x-text="message"></p>
                            </form>
                        </div>
                    </div>

            {{-- Modal: Formulario de creación de producto --}}
            <div
                x-show="showCreator"
                x-cloak
                @keydown.escape.window="showCreator = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                <div
                    @click.stop
                    class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl border border-emerald-200 bg-white p-6 shadow-2xl dark:border-emerald-800 dark:bg-gray-900"
                    x-data="productCreator">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Crear nuevo producto</h3>
                        <button
                            @click="showCreator = false; resetForm()"
                            type="button"
                            class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="createProduct" class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="new_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del producto *</label>
                                <input
                                    type="text"
                                    id="new_name"
                                    x-model="formData.name"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                    required>
                            </div>
                            <div>
                                <label for="new_brand" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Marca</label>
                                <input
                                    type="text"
                                    id="new_brand"
                                    x-model="formData.brand"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            </div>
                            <div>
                                <label for="new_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unidad de medida *</label>
                                <select
                                    id="new_unit"
                                    x-model="formData.unit_base"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                    required>
                                    <option value="unidad">Unidad</option>
                                    <option value="g">Gramos (g)</option>
                                    <option value="kg">Kilogramos (kg)</option>
                                    <option value="ml">Mililitros (ml)</option>
                                    <option value="L">Litros (L)</option>
                                    <option value="paquete">Paquete</option>
                                    <option value="caja">Caja</option>
                                </select>
                            </div>
                            <div>
                                <label for="new_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de producto</label>
                                <select
                                    id="new_type"
                                    x-model="formData.type_id"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">-- Sin tipo --</option>
                                    @foreach($productTypes ?? [] as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="new_barcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código de barras</label>
                                <input
                                    type="text"
                                    id="new_barcode"
                                    x-model="formData.barcode"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            </div>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button
                                type="submit"
                                :disabled="loading || !formData.name || !formData.unit_base"
                                :class="loading ? 'opacity-50 cursor-not-allowed' : ''"
                                class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                <span x-show="loading">⏳</span>
                                <span x-text="loading ? 'Creando...' : 'Crear y asociar'"></span>
                            </button>
                            <button
                                type="button"
                                @click="showCreator = false; resetForm()"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                Cancelar
                            </button>
                        </div>
                        <p x-show="message" :class="messageType === 'success' ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'" class="text-sm font-medium" x-text="message"></p>
                    </form>
                </div>
            </div>

            <div class="p-6">
                <div id="products-list">
                    @if($locationProducts->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">No hay productos asignados a esta ubicación.</p>
                    @else
                        <ul class="grid gap-3 sm:grid-cols-2">
                            @foreach($locationProducts as $product)
                                <li class="flex items-center justify-between rounded-lg border border-gray-100 p-3 text-sm dark:border-gray-800">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</p>
                                        @if($product->brand)
                                            <p class="text-xs text-gray-500">{{ $product->brand }}</p>
                                        @endif
                                    </div>
                                    <a href="{{ route('food.products.show', $product) }}" class="text-xs font-semibold text-emerald-600 hover:underline">Ver producto</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
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
