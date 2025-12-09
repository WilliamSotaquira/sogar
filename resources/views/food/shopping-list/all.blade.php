@php
    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
    $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
    $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
@endphp

<x-layouts.app :title="__('Mis Listas de Compra')">
    <div class="mx-auto w-full max-w-7xl space-y-6">
        {{-- Header --}}
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-8 shadow-lg dark:from-emerald-600 dark:to-teal-700">
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
                        <p class="text-xs text-gray-500">Listas totales</p>
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
                        <p class="text-xs text-gray-500">Listas activas</p>
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
                        <p class="text-xs text-gray-500">Items totales</p>
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
                        <p class="text-xs text-gray-500">Comprados</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grid de Listas --}}
        <div class="grid grid-cols-1 gap-4 max-w-4xl mx-auto w-full">
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

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 hover:shadow-md transition-all group">
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0 pr-3">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-lg">{{ $listIcon }}</span>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 group-hover:text-emerald-600 transition truncate" title="{{ $list->name }}">
                                    {{ Str::limit($list->name, 30) }}
                                </h3>
                            </div>
                            <p class="text-xs text-gray-500">
                                {{ $list->generated_at?->format('d M Y, H:i') }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 justify-end">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-300">
                                {{ $statusLabel }}
                            </span>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                {{ ucfirst($list->list_type ?? 'general') }}
                            </span>
                            @if($list->familyGroup)
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200">
                                    👨‍👩‍👧 {{ Str::limit($list->familyGroup->name, 18) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Progreso --}}
                    <div class="mb-4">
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
                    <div class="grid grid-cols-3 gap-3 mb-4 text-sm">
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Items</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $list->items->count() }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Estimado</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">${{ number_format($estimatedTotal ?: $list->estimated_budget, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Gastado</p>
                            <p class="font-semibold {{ $budgetAmount && $actualTotal > $budgetAmount ? 'text-rose-600' : 'text-emerald-600 dark:text-emerald-300' }}">
                                ${{ number_format($actualTotal, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Budget Info --}}
                    @if($list->budget)
                        <div class="mb-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between mb-2 text-sm">
                                <span class="font-semibold text-gray-800 dark:text-gray-100">Presupuesto: {{ $list->budget->category->name }}</span>
                                <span class="text-xs text-gray-500">Disponible: ${{ number_format(max(0, $budgetAmount - $actualTotal), 0, ',', '.') }}</span>
                            </div>
                            @if($budgetUsage !== null)
                                <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full {{ $budgetUsage >= 95 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $budgetUsage }}%"></div>
                                </div>
                                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ round($budgetUsage) }}% del presupuesto usado
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <a href="{{ route('food.shopping-list.show', $list) }}" class="{{ $btnPrimary }} flex-1 text-center">
                            Ver Lista
                        </a>
                        <button type="button" onclick="generateSuggestions({{ $list->id }})" class="{{ $btnSecondary }}" title="Generar sugeridos">
                            🤖
                        </button>
                        <button type="button" onclick="deleteList({{ $list->id }})" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-800 dark:bg-gray-900 dark:text-rose-400" title="Eliminar lista">
                            🗑️
                        </button>
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
    </div>

    {{-- Modal: Crear Lista --}}
    <div id="create-list-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onclick="if(event.target===this) closeCreateListModal()" role="dialog" aria-modal="true" aria-labelledby="create-list-title">
        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 max-w-md w-full shadow-xl" tabindex="-1">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">➕ Nueva Lista de Compra</h3>

            <form action="{{ route('food.shopping-list.generate') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="list_name" class="{{ $label }}" id="create-list-title">Nombre de la lista *</label>
                    <input id="list_name" type="text" name="name" required class="{{ $input }}" placeholder="Ej: Mercado Semanal, Aseo, Ferretería" value="Compra {{ now()->format('d/m') }}">
                    <p class="text-xs text-gray-500 mt-1">Puedes personalizarlo como desees</p>
                </div>

                <div>
                    <label for="list_type" class="{{ $label }}">Tipo de lista</label>
                    <select id="list_type" name="list_type" class="{{ $input }}">
                        <option value="general">General</option>
                        <option value="food">Alimentos</option>
                        <option value="cleaning">Aseo</option>
                        <option value="maintenance">Mantenimiento/Arreglos</option>
                        <option value="other">Otro</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Ayuda a organizar tus listas</p>
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

                <div class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    <input type="checkbox" name="auto_suggest" id="auto-suggest" value="1" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" checked aria-describedby="auto-suggest-help">
                    <label for="auto-suggest" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer" id="auto-suggest-help">
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
            const nameInput = document.getElementById('list_name');
            if (nameInput) {
                nameInput.focus();
            }
        }

        function closeCreateListModal() {
            document.getElementById('create-list-modal').classList.add('hidden');
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

        async function deleteList(listId) {
            if (!confirm('¿Eliminar esta lista de compra?')) return;

            try {
                const res = await fetch(`/food/shopping-list/${listId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                if (res.ok) {
                    location.reload();
                } else {
                    alert('Error al eliminar');
                }
            } catch (err) {
                console.error(err);
                alert('Error al eliminar');
            }
        }

        async function generateSuggestions(listId) {
            if (!confirm('¿Generar sugeridos automáticos para esta lista basados en productos con stock bajo?')) return;

            try {
                const res = await fetch(`/food/shopping-list/${listId}/suggest`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                if (res.ok) {
                    const data = await res.json();
                    alert(`✅ ${data.count || 0} productos sugeridos agregados`);
                    location.reload();
                } else {
                    alert('Error al generar sugeridos');
                }
            } catch (err) {
                console.error(err);
                alert('Error al generar sugeridos');
            }
        }
    </script>
    @endpush
</x-layouts.app>
