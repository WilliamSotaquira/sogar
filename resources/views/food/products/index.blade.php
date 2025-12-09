<x-layouts.app :title="__('Productos')">
    @php
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
    @endphp

    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-8 shadow-lg dark:from-emerald-600 dark:to-teal-700">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Inventario doméstico</p>
                    <h1 class="text-3xl font-bold">Catálogo de Productos</h1>
                    <p class="text-sm text-white/80">Consulta rápido nombre, marca, código y precio de cada producto.</p>
                </div>
                <a href="{{ route('food.products.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white text-emerald-600 px-4 py-2 text-sm font-semibold shadow-sm hover:bg-white/90 transition">
                    ➕ Nuevo Producto
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if($products->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 bg-white text-center p-10 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-center">
                    <span class="text-4xl">📦</span>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Aún no tienes productos</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Agrega tu primer producto para empezar a llevar el control de inventario y compras inteligentes.
                </p>
                <a href="{{ route('food.products.create') }}" class="{{ $btnPrimary }}">
                    ➕ Crear Producto
                </a>
            </div>
        @else
            <div class="rounded-xl border border-gray-200 bg-white shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Listado de Productos</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $products->count() }} productos registrados</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Producto</th>
                                <th class="px-4 py-3 text-left font-semibold">Marca</th>
                                <th class="px-4 py-3 text-left font-semibold">Código</th>
                                <th class="px-4 py-3 text-left font-semibold">Presentación</th>
                                <th class="px-4 py-3 text-left font-semibold">Precio actual</th>
                                <th class="px-4 py-3 text-left font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($products as $product)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</p>
                                            @if($product->type)
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium mt-1"
                                                      style="background-color: {{ $product->type->color }}1A; color: {{ $product->type->color }};">
                                                    {{ $product->type->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                        {{ $product->brand ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($product->barcode)
                                            <span class="font-mono text-sm text-gray-900 dark:text-gray-100">{{ $product->barcode }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                        {{ $product->presentation_label }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                        @if($product->current_price?->price_per_base)
                                            ${{ number_format($product->current_price->price_per_base, 2) }}
                                            <p class="text-[11px] text-gray-500">
                                                {{ $product->current_price->vendor ?: 'Sin vendor' }}
                                            </p>
                                        @else
                                            <span class="text-gray-400 text-sm">Sin precio</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('food.products.show', $product) }}" class="{{ $btnSecondary }}">Ver</a>
                                            <a href="{{ route('food.products.edit', $product) }}" class="{{ $btnSecondary }}">Editar</a>
                                            <form action="{{ route('food.products.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Eliminar este producto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-800 dark:bg-gray-900 dark:text-rose-300">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
