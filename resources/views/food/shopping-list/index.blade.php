<x-layouts.app :title="__('Lista de compra')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
    @endphp
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Compras inteligentes</p>
                    <h1 class="text-3xl font-bold">Lista activa</h1>
                    <p class="text-sm text-white/80">Genera según consumo y marca en vivo mientras compras.</p>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2 space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">
                            {{ $list?->name ?? 'Sin lista activa' }}
                        </h2>
                        <form method="POST" action="{{ route('food.shopping-list.generate') }}" class="flex flex-wrap items-center gap-2">
                            @csrf
                            <input type="number" name="horizon_days" min="1" max="30" placeholder="Días" class="h-10 w-20 rounded-lg border border-gray-200 bg-white px-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <input type="number" name="people_count" min="1" max="10" placeholder="Personas" class="h-10 w-24 rounded-lg border border-gray-200 bg-white px-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <button type="submit" class="{{ $btnPrimary }}">Generar</button>
                        </form>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @if($list)
                            Generada {{ $list->generated_at?->diffForHumans() }} · Compra estimada {{ $list->expected_purchase_on?->format('d/m') }}
                        @else
                            Genera una lista para comenzar.
                        @endif
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50">Items</h3>
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            @if($list)
                                {{ $list->items->where('is_checked', true)->count() }} / {{ $list->items->count() }} comprados
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 space-y-2">
                        @forelse($list?->items ?? [] as $item)
                            <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                                <form method="POST" action="{{ route('food.shopping-list.items.mark', [$list, $item->id]) }}" class="flex items-start gap-3">
                                    @csrf
                                    <input type="hidden" name="is_checked" value="{{ $item->is_checked ? 0 : 1 }}">
                                    <button type="submit" class="mt-1 h-5 w-5 rounded-md border {{ $item->is_checked ? 'bg-emerald-500 border-emerald-500' : 'border-gray-300 dark:border-gray-700' }}"></button>
                                    <div class="flex-1 space-y-1">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $item->name }}</p>
                                            <span class="text-xs rounded-full px-2 py-1 {{ $item->priority === 'high' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-100' : ($item->priority === 'medium' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-100' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200') }}">
                                                {{ ucfirst($item->priority) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Sugerido: {{ $item->qty_unit_label ?? $item->qty_to_buy_base }} · Stock actual: {{ $item->qty_current_base }} {{ $item->unit_base }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Estimado: ${{ number_format($item->estimated_price, 2, ',', '.') }}</p>
                                    </div>
                                </form>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Genera una lista para ver sugerencias.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50">Resumen</h3>
                    @if($list)
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Presupuesto estimado: ${{ number_format($list->estimated_budget, 2, ',', '.') }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Personas: {{ $list->people_count }} · Horizonte: {{ $list->purchase_frequency_days }} días</p>
                    @else
                        <p class="text-sm text-gray-500 mt-2">Aún no hay lista activa.</p>
                    @endif
                </div>
                @if($list)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50">Acciones</h3>
                        <form method="POST" action="{{ route('food.shopping-list.sync') }}" class="mt-3 space-y-2">
                            @csrf
                            <button type="submit" class="{{ $btnPrimary }} w-full">Sincronizar a inventario</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
