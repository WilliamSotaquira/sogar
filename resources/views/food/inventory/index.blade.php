<x-layouts.app :title="__('Inventario de Alimentos')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
    @endphp
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Inventario</p>
                    <h1 class="text-3xl font-bold">Stock por ubicación</h1>
                    <p class="text-sm text-white/80">Filtra por alacena o tipo y revisa caducidades.</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <form class="flex flex-wrap gap-2 items-center">
                <select name="location_id" class="{{ $input }}">
                    <option value="">Todas las ubicaciones</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected(request('location_id') == $loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
                <select name="type_id" class="{{ $input }}">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" @selected(request('type_id') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
                <button class="{{ $btnSecondary }}" type="submit">Filtrar</button>
            </form>
            <a href="{{ route('food.products.index') }}" class="{{ $btnSecondary }}">Productos</a>
            <a href="{{ route('food.purchases.index') }}" class="{{ $btnSecondary }}">Compras</a>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Lotes</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-100 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr class="text-left text-xs uppercase text-gray-500">
                            <th class="px-3 py-2 font-semibold">Producto</th>
                            <th class="px-3 py-2 font-semibold">Ubicación</th>
                            <th class="px-3 py-2 font-semibold">Tipo</th>
                            <th class="px-3 py-2 font-semibold">Qty</th>
                            <th class="px-3 py-2 font-semibold">Caduca</th>
                            <th class="px-3 py-2 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                            @php
                                $expires = $batch->expires_on ? \Carbon\Carbon::parse($batch->expires_on) : null;
                                $days = $expires ? now()->diffInDays($expires, false) : null;
                            @endphp
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="px-3 py-2 font-medium">{{ $batch->product->name }}</td>
                                <td class="px-3 py-2">{{ $batch->location?->name ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $batch->product->type?->name ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $batch->qty_remaining_base }} {{ $batch->unit_base }}</td>
                                <td class="px-3 py-2">
                                    @if($expires)
                                        {{ $expires->format('d M') }}
                                        <span class="text-xs text-gray-500">({{ $days }} días)</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <span class="hero-chip text-xs">
                                        @if($batch->status === 'ok')
                                            OK
                                        @else
                                            {{ $batch->status }}
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-gray-500">Aún no hay stock.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
