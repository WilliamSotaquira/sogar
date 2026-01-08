@php
    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
    $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
    $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
    $btnIcon = 'inline-flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
    $btnIconDanger = 'inline-flex h-11 w-11 items-center justify-center rounded-xl border border-rose-200 bg-white text-rose-600 shadow-sm transition hover:bg-rose-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 dark:border-rose-800 dark:bg-gray-900 dark:text-rose-400 dark:hover:bg-rose-900/20';
@endphp

<x-layouts.app :title="__('Mis Listas de Compra')">
    <div class="mx-auto w-full max-w-7xl space-y-4 px-3 sm:px-0">
        @php
            $listTypeLabels = collect($listTypes ?? [])->pluck('name', 'slug');
            $defaultListName = 'Compra semanal - ' . now()->locale('es')->translatedFormat('j M');
        @endphp
        {{-- Header --}}
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 sm:p-8 shadow-lg dark:from-emerald-600 dark:to-teal-700">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Compras Inteligentes</p>
                    <h1 class="text-3xl font-bold">Mis Listas de Compra</h1>
                    <p class="text-sm text-white/80">Organiza múltiples listas: Mercado, Aseo, Reparaciones y más</p>
                </div>
                <button type="button" onclick="openCreateListModal()" aria-haspopup="dialog" aria-controls="create-list-modal" class="inline-flex items-center gap-2 rounded-xl bg-white text-emerald-600 px-4 py-2 text-sm font-semibold shadow-sm hover:bg-white/90 transition">
                    ➕ Nueva Lista
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 dark:border-rose-900/50 dark:bg-rose-900/30 dark:text-rose-100">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p id="page-status" class="hidden rounded-lg border px-4 py-3 text-sm" role="status" aria-live="polite" aria-atomic="true"></p>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Importar lista</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Carga un archivo .csv (ideal para Google Sheets) o .json exportado desde una lista.</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">CSV mínimo: columnas <span class="font-medium">producto</span> y <span class="font-medium">cantidad</span>. Opcional: <span class="font-medium">lista</span>, <span class="font-medium">tipo</span>, <span class="font-medium">codigo</span>. <a href="{{ route('food.shopping-list.templateCsv') }}" class="text-sm font-semibold text-emerald-700 underline decoration-emerald-200 underline-offset-2 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-emerald-300 dark:hover:text-emerald-200">Descargar plantilla CSV</a></p>
                </div>
                <form method="POST" action="{{ route('food.shopping-list.import') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2 sm:items-end">
                    @csrf
                    <div class="sm:flex-1">
                        <label for="import-file" class="{{ $label }}">Archivo</label>
                        <input id="import-file" name="file" type="file" accept="text/csv,.csv,application/json,.json" class="{{ $input }} cursor-pointer overflow-hidden file:mr-3 file:h-11 file:cursor-pointer file:rounded-l-xl file:border-0 file:bg-gray-100 file:px-4 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-700 dark:file:text-gray-100 dark:hover:file:bg-gray-600" required>
                    </div>
                    <button type="submit" class="{{ $btnPrimary }} h-11 shrink-0">Importar</button>
                </form>
            </div>
        </div>

        {{-- Métricas Rápidas --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $totalLists = $lists->count();
                $activeLists = $lists->where('status', 'active')->count();
                $totalItems = $lists->sum(function($list) { return $list->items->count(); });
                $checkedItems = $lists->sum(function($list) { return $list->items->where('is_checked', true)->count(); });
            @endphp

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <span class="text-2xl">📋</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalLists }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Listas totales</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <span class="text-2xl">✅</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $activeLists }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Listas activas</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <span class="text-2xl">🛒</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalItems }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Items totales</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <span class="text-2xl">✔️</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $checkedItems }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Comprados</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Listas (cards en móvil) --}}
        <div class="grid grid-cols-1 gap-4 md:hidden">
            @forelse($lists as $list)
                @php
                    $progress = $list->items->count() > 0 ? ($list->items->where('is_checked', true)->count() / $list->items->count()) * 100 : 0;
                    $statusColors = [
                        'active' => 'emerald',
                        'completed' => 'blue',
                        'cancelled' => 'gray',
                        'closed' => 'gray',
                    ];
                    $color = $statusColors[$list->status] ?? 'gray';

                    // Iconos según el tipo de lista
                    $typeIcons = [
                        'food' => '🍎',
                        'cleaning' => '🧽',
                        'maintenance' => '🔧',
                        'general' => '📋',
                        'other' => '📄',
                    ];
                    $listIcon = $typeIcons[$list->list_type ?? 'general'] ?? '📋';
                    $typeLabel = $listTypeLabels[$list->list_type] ?? ($list->list_type ? ucfirst(str_replace('-', ' ', $list->list_type)) : 'General');

                    // Traducción de status
                    $statusLabels = [
                        'active' => 'Activa',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'closed' => 'Cerrada',
                    ];
                    $statusLabel = $statusLabels[$list->status] ?? ucfirst($list->status);
                    $budgetAmount = $list->budget?->amount ?? 0;
                    $estimatedTotal = $list->items->sum(fn($item) => $item->estimated_price ?? 0);
                    $actualTotal = $list->actual_total ?? 0;
                    $budgetUsage = $budgetAmount > 0 ? min(100, ($actualTotal / $budgetAmount) * 100) : null;
                @endphp

                <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 hover:shadow-md transition-all group">
                    {{-- Header --}}
                    <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 sm:flex-1 sm:pr-3">
                            <div class="flex items-start gap-2 mb-1">
                                <span class="text-lg flex-shrink-0">{{ $listIcon }}</span>
                                <a href="{{ route('food.shopping-list.show', $list) }}"
                                   class="relative z-10 pointer-events-auto text-base font-semibold leading-snug text-gray-900 underline decoration-gray-300 underline-offset-2 hover:text-emerald-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-gray-100 dark:hover:text-emerald-200 transition break-words"
                                   title="{{ $list->name }}">
                                    {{ $list->name }}
                                </a>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                {{ $list->generated_at?->format('d M Y, H:i') }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 sm:justify-end">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-300">
                                {{ $statusLabel }}
                            </span>
                            <span class="inline-flex max-w-[9.5rem] items-center rounded-full px-2 py-0.5 text-[11px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200 truncate sm:max-w-none">
                                {{ $typeLabel }}
                            </span>
                            @if($list->familyGroup)
                                <span class="inline-flex max-w-[9.5rem] items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200 truncate sm:max-w-none">
                                    👨‍👩‍👧 {{ $list->familyGroup->name }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Progreso --}}
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                {{ $list->items->where('is_checked', true)->count() }}/{{ $list->items->count() }} items
                            </span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ round($progress) }}%
                            </span>
                        </div>
                        <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 transition-all" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    {{-- Totales rápidos --}}
                    <div class="grid grid-cols-3 gap-3 mb-5 text-sm">
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200">
                            <p class="text-[11px] uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">Items</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100 tabular-nums">{{ $list->items->count() }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200">
                            <p class="text-[11px] uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">Estimado</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100 tabular-nums">${{ number_format($estimatedTotal ?: $list->estimated_budget, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200">
                            <p class="text-[11px] uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">Gastado</p>
                            <p class="font-semibold tabular-nums {{ $budgetAmount && $actualTotal > $budgetAmount ? 'text-rose-600 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                                ${{ number_format($actualTotal, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Budget Info --}}
                    @if($list->budget)
                        <div class="mb-5 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between mb-2 text-sm">
                                <span class="min-w-0 font-semibold text-gray-800 dark:text-gray-100 break-words">Presupuesto: {{ $list->budget->category->name }}</span>
                                <span class="text-xs text-gray-700 dark:text-gray-300 tabular-nums">Disponible: ${{ number_format(max(0, $budgetAmount - $actualTotal), 0, ',', '.') }}</span>
                            </div>
                            @if($budgetUsage !== null)
                                <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full {{ $budgetUsage >= 95 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $budgetUsage }}%"></div>
                                </div>
                                <p class="mt-1 text-[11px] text-gray-600 dark:text-gray-400">
                                    {{ round($budgetUsage) }}% del presupuesto usado
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex flex-col gap-2">
                        <div class="relative">
                            <button type="button"
                                    class="{{ $btnIcon }} relative z-10"
                                    title="Acciones"
                                    aria-label="Acciones"
                                    data-menu-trigger
                                    data-menu-panel="list-menu-{{ $list->id }}">
                                ⋯
                            </button>
                            <div id="list-menu-{{ $list->id }}"
                                 class="hidden w-44 rounded-lg border border-gray-200 bg-white p-1 text-sm shadow-xl z-50 dark:border-gray-800 dark:bg-gray-900"
                                 data-menu-panel>
                                <a href="{{ route('food.shopping-list.exportCsv', $list) }}"
                                   class="w-full block rounded-lg px-3 py-2 text-left font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
                                    ⬇️ Exportar (CSV)
                                </a>
                                <a href="{{ route('food.shopping-list.export', $list) }}"
                                   class="w-full block rounded-lg px-3 py-2 text-left font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
                                    ⬇️ Exportar (JSON)
                                </a>
                                <form action="{{ route('food.shopping-list.suggest', $list) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="w-full rounded-lg px-3 py-2 text-left font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
                                        🤖 Agregar sugeridos
                                    </button>
                                </form>
                                <form action="{{ route('food.shopping-list.destroy', $list) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full rounded-lg px-3 py-2 text-left font-medium text-rose-600 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-900/20">
                                        🗑️ Eliminar lista
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 lg:col-span-3 text-center py-12">
                    <div class="inline-flex h-20 w-20 rounded-full bg-gray-100 dark:bg-gray-800 items-center justify-center mb-4">
                        <span class="text-4xl">📋</span>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">No tienes listas de compra aún</p>
                    <button type="button" onclick="openCreateListModal()" class="{{ $btnPrimary }}">
                        ➕ Crear mi primera lista
                    </button>
                </div>
            @endforelse
        </div>

        {{-- Listas (tabla en desktop) --}}
        <div class="hidden md:block">
            <div class="overflow-x-auto md:overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <table class="w-full text-sm md:overflow-visible">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <th class="px-4 py-3">Lista</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Progreso</th>
                            <th class="px-4 py-3">Items</th>
                            <th class="px-4 py-3">Estimado</th>
                            <th class="px-4 py-3">Gastado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($lists as $list)
                            @php
                                $progress = $list->items->count() > 0 ? ($list->items->where('is_checked', true)->count() / $list->items->count()) * 100 : 0;
                                $statusColors = [
                                    'active' => 'emerald',
                                    'completed' => 'blue',
                                    'cancelled' => 'gray',
                                    'closed' => 'gray',
                                ];
                                $color = $statusColors[$list->status] ?? 'gray';
                                $typeIcons = [
                                    'food' => '🍎',
                                    'cleaning' => '🧽',
                                    'maintenance' => '🔧',
                                    'general' => '📋',
                                    'other' => '📄',
                                ];
                                $listIcon = $typeIcons[$list->list_type ?? 'general'] ?? '📋';
                                $typeLabel = $listTypeLabels[$list->list_type] ?? ($list->list_type ? ucfirst(str_replace('-', ' ', $list->list_type)) : 'General');
                                $statusLabels = [
                                    'active' => 'Activa',
                                    'completed' => 'Completada',
                                    'cancelled' => 'Cancelada',
                                    'closed' => 'Cerrada',
                                ];
                                $statusLabel = $statusLabels[$list->status] ?? ucfirst($list->status);
                                $budgetAmount = $list->budget?->amount ?? 0;
                                $estimatedTotal = $list->items->sum(fn($item) => $item->estimated_price ?? 0);
                                $actualTotal = $list->actual_total ?? 0;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                <td class="px-4 py-3">
                                    <div class="flex items-start gap-2">
                                        <span class="text-lg">{{ $listIcon }}</span>
                                        <div>
                                            <a href="{{ route('food.shopping-list.show', $list) }}"
                                               class="relative z-10 pointer-events-auto font-semibold text-gray-900 underline decoration-gray-300 underline-offset-2 hover:text-emerald-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-gray-100 dark:hover:text-emerald-200">
                                                {{ $list->name }}
                                            </a>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $list->generated_at?->format('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <span class="inline-flex max-w-[10rem] items-center rounded-full px-2 py-0.5 text-[11px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200 truncate">
                                            {{ $typeLabel }}
                                        </span>
                                        @if($list->familyGroup)
                                            <span class="inline-flex max-w-[10rem] items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200 truncate">
                                                👨‍👩‍👧 {{ $list->familyGroup->name }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-300">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-600 dark:text-gray-300">
                                            {{ $list->items->where('is_checked', true)->count() }}/{{ $list->items->count() }}
                                        </span>
                                        <div class="h-2 w-24 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-full bg-emerald-500 transition-all" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ round($progress) }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 tabular-nums text-gray-700 dark:text-gray-200">{{ $list->items->count() }}</td>
                                <td class="px-4 py-3 tabular-nums text-gray-700 dark:text-gray-200">${{ number_format($estimatedTotal ?: $list->estimated_budget, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 tabular-nums {{ $budgetAmount && $actualTotal > $budgetAmount ? 'text-rose-600 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                                    ${{ number_format($actualTotal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 relative overflow-visible">
                                    <div class="relative flex items-center justify-end">
                                        <div class="relative">
                                            <button type="button"
                                                    class="{{ $btnIcon }} h-9 w-9 relative z-10"
                                                    title="Acciones"
                                                    aria-label="Acciones"
                                                    data-menu-trigger
                                                    data-menu-panel="list-menu-table-{{ $list->id }}">
                                                ⋯
                                            </button>
                                            <div id="list-menu-table-{{ $list->id }}"
                                                 class="hidden w-44 rounded-lg border border-gray-200 bg-white p-1 text-sm shadow-xl z-50 dark:border-gray-800 dark:bg-gray-900"
                                                 data-menu-panel>
                                                <a href="{{ route('food.shopping-list.exportCsv', $list) }}"
                                                   class="w-full block rounded-lg px-3 py-2 text-left font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
                                                    ⬇️ Exportar (CSV)
                                                </a>
                                                <a href="{{ route('food.shopping-list.export', $list) }}"
                                                   class="w-full block rounded-lg px-3 py-2 text-left font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
                                                    ⬇️ Exportar (JSON)
                                                </a>
                                                <form action="{{ route('food.shopping-list.suggest', $list) }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                            class="w-full rounded-lg px-3 py-2 text-left font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
                                                        🤖 Agregar sugeridos
                                                    </button>
                                                </form>
                                                <form action="{{ route('food.shopping-list.destroy', $list) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="w-full rounded-lg px-3 py-2 text-left font-medium text-rose-600 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-900/20">
                                                        🗑️ Eliminar lista
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <p class="mb-2">No tienes listas de compra aún</p>
                                    <button type="button" onclick="openCreateListModal()" class="{{ $btnPrimary }}">
                                        ➕ Crear mi primera lista
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


    {{-- Modal: Crear Lista --}}
    <div id="create-list-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" style="display: none;" onclick="if(event.target===this) closeCreateListModal()" role="dialog" aria-modal="true" aria-labelledby="create-list-title" aria-hidden="true">
        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 max-w-md w-full shadow-xl" tabindex="-1">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">➕ Nueva Lista de Compra</h3>

            <form action="{{ route('food.shopping-list.generate') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="list_name" class="{{ $label }}" id="create-list-title">Nombre de la lista *</label>
                    <input id="list_name"
                           type="text"
                           name="name"
                           required
                           class="{{ $input }}"
                           placeholder="Ej: Mercado Semanal, Aseo, Ferretería"
                           value="{{ $defaultListName }}"
                           data-list-name-input
                           data-date-label="{{ now()->locale('es')->translatedFormat('j M') }}"
                           data-auto-name="1">
                    <p class="text-xs text-gray-500 mt-1">Puedes personalizarlo como desees</p>
                </div>

                <div data-list-type-field>
                    <div class="flex items-end justify-between gap-3">
                        <label for="list_type" class="{{ $label }}">
                            Tipo de lista <span class="text-rose-500">*</span>
                        </label>
                        <button type="button"
                                class="text-xs font-semibold text-emerald-700 underline decoration-emerald-200 underline-offset-2 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-emerald-300 dark:hover:text-emerald-200"
                                data-list-type-toggle
                                aria-expanded="false"
                                aria-controls="list-type-panel-modal">
                            Agregar tipo
                        </button>
                    </div>

                    <select id="list_type" name="list_type" required class="{{ $input }}" data-list-type-select>
                        @foreach(($listTypes ?? collect()) as $type)
                            <option value="{{ $type->slug }}">{{ $type->name }}</option>
                        @endforeach
                    </select>

                    <div id="list-type-panel-modal" class="hidden" data-list-type-panel>
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

                <div>
                    <label for="budget_id" class="{{ $label }}">Presupuesto (opcional)</label>
                    <select id="budget_id" name="budget_id" class="{{ $input }}">
                        <option value="">Sin presupuesto asignado</option>
                        @foreach($budgets ?? [] as $budget)
                            <option value="{{ $budget->id }}">
                                {{ $budget->category->name }} - ${{ number_format($budget->amount, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="expected_purchase_on" class="{{ $label }}">Fecha estimada</label>
                        <input id="expected_purchase_on" type="date" name="expected_purchase_on" value="{{ now()->addDays(3)->format('Y-m-d') }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="horizon_days" class="{{ $label }}">Horizonte (días)</label>
                        <input id="horizon_days" type="number" name="horizon_days" min="1" max="30" value="7" class="{{ $input }}">
                    </div>
                </div>

                    <div class="flex items-start gap-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                        <input type="checkbox" name="auto_suggest" id="auto-suggest" value="1" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" checked aria-describedby="auto-suggest-help">
                        <label for="auto-suggest" class="min-w-0 flex-1 break-words text-sm text-gray-700 dark:text-gray-300 cursor-pointer" id="auto-suggest-help">
                            🤖 Generar sugeridos automáticos basados en stock bajo
                        </label>
                    </div>

                <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeCreateListModal()" class="{{ $btnSecondary }} flex-1">
                        Cancelar
                    </button>
                    <button type="submit" class="{{ $btnPrimary }} flex-1">
                        ✓ Crear Lista
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openCreateListModal() {
            const modal = document.getElementById('create-list-modal');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            const nameInput = document.getElementById('list_name');
            if (nameInput) {
                nameInput.focus();
            }
        }

        function closeCreateListModal() {
            const modal = document.getElementById('create-list-modal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }

        // Cerrar modal con tecla Esc
        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                const modal = document.getElementById('create-list-modal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeCreateListModal();
                }
            }
        });

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

                const nameInput = document.querySelector('[data-list-name-input]');
                if (select && nameInput) {
                    const dateLabel = nameInput.getAttribute('data-date-label') || '';
                    const setName = () => {
                        const typeLabel = select.options[select.selectedIndex]?.text || 'Compra semanal';
                        nameInput.value = `${typeLabel} - ${dateLabel}`.trim();
                    };

                    select.addEventListener('change', () => {
                        if (nameInput.getAttribute('data-auto-name') !== '1') return;
                        setName();
                    });

                    nameInput.addEventListener('input', () => {
                        nameInput.setAttribute('data-auto-name', '0');
                    });
                }
            };

            fields.forEach(initField);
        });

        document.addEventListener('DOMContentLoaded', () => {
            const triggers = document.querySelectorAll('[data-menu-trigger]');
            const panels = document.querySelectorAll('[data-menu-panel]');

            const closeAll = () => {
                panels.forEach((panel) => {
                    panel.classList.add('hidden');
                    panel.style.position = '';
                    panel.style.top = '';
                    panel.style.left = '';
                });
            };

            triggers.forEach((btn) => {
                btn.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const panelId = btn.getAttribute('data-menu-panel');
                    if (!panelId) return;
                    const panel = document.getElementById(panelId);
                    if (!panel) return;

                    const isHidden = panel.classList.contains('hidden');
                    closeAll();
                    if (!isHidden) return;

                    const rect = btn.getBoundingClientRect();
                    panel.classList.remove('hidden');
                    panel.style.position = 'fixed';
                    panel.style.maxWidth = 'calc(100vw - 16px)';

                    const viewportWidth = window.innerWidth;
                    const viewportHeight = window.innerHeight;
                    const panelWidth = panel.offsetWidth;
                    const panelHeight = panel.offsetHeight;

                    let top = rect.bottom + 8;
                    let left = rect.right - panelWidth;

                    if (top + panelHeight > viewportHeight - 8) {
                        top = rect.top - panelHeight - 8;
                    }
                    if (left < 8) {
                        left = 8;
                    }
                    if (left + panelWidth > viewportWidth - 8) {
                        left = viewportWidth - panelWidth - 8;
                    }
                    if (top < 8) {
                        top = 8;
                    }

                    panel.style.top = `${Math.round(top)}px`;
                    panel.style.left = `${Math.round(left)}px`;
                });
            });

            document.addEventListener('click', closeAll);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeAll();
                }
            });
            window.addEventListener('resize', closeAll);
            window.addEventListener('scroll', closeAll, true);
        });
    </script>
    @endpush
</x-layouts.app>
