<x-layouts.app :title="__('Ubicaciones del inventario')">
    @php
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
    @endphp

    <div class="mx-auto w-full max-w-5xl space-y-6">
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-4 text-white sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Inventario doméstico</p>
                    <h1 class="text-3xl font-bold">Ubicaciones físicas</h1>
                    <p class="text-sm text-white/80">Controla los espacios donde almacenas tus productos (nevera, alacena, baño...)</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('food.locations.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                        ➕ Nueva ubicación
                    </a>
                    <a href="{{ route('food.inventory.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                        📦 Ver inventario
                    </a>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Listado de ubicaciones</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cada fila representa un espacio físico del hogar.</p>
                </div>
                <a href="{{ route('food.locations.create') }}" class="{{ $btnPrimary }}">+ Agregar</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Ubicación</th>
                            <th class="px-4 py-3 text-left font-semibold">Color</th>
                            <th class="px-4 py-3 text-left font-semibold">Orden</th>
                            <th class="px-4 py-3 text-left font-semibold">Default</th>
                            <th class="px-4 py-3 text-left font-semibold">Productos</th>
                            <th class="px-4 py-3 text-left font-semibold">Lotes</th>
                            <th class="px-4 py-3 text-right font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($locations as $location)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                <td class="px-4 py-4">
                                    <a href="{{ route('food.locations.show', $location) }}" class="font-semibold text-gray-900 transition hover:text-emerald-600 dark:text-gray-100 dark:hover:text-emerald-300">
                                        {{ $location->name }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Slug: {{ $location->slug }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="h-4 w-4 rounded-full border border-gray-200 dark:border-gray-700" style="background-color: {{ $location->color ?: '#94a3b8' }};"></span>
                                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ $location->color ?: '—' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-gray-700 dark:text-gray-200">
                                    {{ $location->sort_order }}
                                </td>
                                <td class="px-4 py-4">
                                    @if($location->is_default)
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Predeterminada</span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-gray-900 dark:text-gray-100 font-semibold">
                                    {{ $location->products_count }}
                                </td>
                                <td class="px-4 py-4 text-gray-900 dark:text-gray-100 font-semibold">
                                    {{ $location->batches_count }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end">
                                        <a href="{{ route('food.locations.edit', $location) }}" class="{{ $btnSecondary }}">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Aún no tienes ubicaciones registradas. Crea la primera para comenzar a clasificar tu inventario.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
