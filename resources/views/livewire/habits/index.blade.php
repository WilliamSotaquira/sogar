<div>
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="rounded-xl bg-gradient-to-br from-indigo-500 to-fuchsia-600 p-8 shadow-lg dark:from-indigo-600 dark:to-fuchsia-700">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold text-white/90">Productividad</p>
                    <h1 class="text-3xl font-bold">Hábitos y tareas</h1>
                    <p class="text-sm text-white/85">Registra avances, mira rachas y proyecciones por periodo.</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-white/85">
                        <span class="hero-chip">Check-in rápido</span>
                        <span class="hero-chip">Objetivos por periodo</span>
                        <span class="hero-chip">Proyección lineal</span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a
                        href="{{ route('habits.index', ['view' => 'cards']) }}"
                        wire:navigate
                        class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm ring-1 ring-white/20 hover:bg-white/20 {{ $view === 'cards' ? 'bg-white/25 text-white' : 'bg-white/10 text-white/90' }}"
                        aria-current="{{ $view === 'cards' ? 'page' : 'false' }}"
                    >Tarjetas</a>
                    <a
                        href="{{ route('habits.index', ['view' => 'list']) }}"
                        wire:navigate
                        class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm ring-1 ring-white/20 hover:bg-white/20 {{ $view === 'list' ? 'bg-white/25 text-white' : 'bg-white/10 text-white/90' }}"
                        aria-current="{{ $view === 'list' ? 'page' : 'false' }}"
                    >Lista</a>
                    <button
                        type="button"
                        wire:click="openForm"
                        class="inline-flex items-center justify-center rounded-lg bg-white/15 px-4 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-white/20 hover:bg-white/20"
                    >
                        + Crear
                    </button>
                </div>
            </div>
        </div>

        @if ($showForm)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $editingId ? 'Editar' : 'Nueva' }} actividad</h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Define el objetivo (meta) por periodo y cómo se registra.</p>
                    </div>
                    <button type="button" wire:click="closeForm" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Cerrar</button>
                </div>

                <form wire:submit.prevent="save" class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Título *</label>
                        <input
                            type="text"
                            wire:model="title"
                            class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="Ej: Leer 20 minutos"
                        >
                        @error('title') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo *</label>
                        <select wire:model="kind" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none">
                            <option value="habit">Hábito</option>
                            <option value="task">Tarea</option>
                        </select>
                        @error('kind') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Periodo *</label>
                        <select wire:model="cadence" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none">
                            <option value="daily">Diario</option>
                            <option value="weekly">Semanal</option>
                            <option value="monthly">Mensual</option>
                            <option value="once">Único</option>
                        </select>
                        @error('cadence') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta (cantidad) *</label>
                        <input type="number" min="1" step="1" wire:model="target_count" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        @error('target_count') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unidad</label>
                        <input type="text" wire:model="unit" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" placeholder="Ej: check, min, páginas">
                        @error('unit') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vence el (opcional)</label>
                        <input type="date" wire:model="due_on" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        @error('due_on') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2 flex flex-wrap items-center gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800">
                            Activo
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model="is_shared" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800">
                            Compartido con mi núcleo activo
                        </label>
                    </div>

                    <div class="md:col-span-2 flex gap-2 pt-1">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                            {{ $editingId ? 'Guardar cambios' : 'Crear' }}
                        </button>
                        <button type="button" wire:click="closeForm" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        @endif

        @if ($view === 'list')
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-x-auto">
                <table class="min-w-[980px] w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">
                            <th class="px-4 py-3">Actividad</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Ámbito</th>
                            <th class="px-4 py-3">Progreso</th>
                            <th class="px-4 py-3">Proyección</th>
                            <th class="px-4 py-3">Racha</th>
                            <th class="px-4 py-3">Vínculo</th>
                            <th class="px-4 py-3">Vence</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($activities as $activity)
                            @php
                                $m = $metrics[$activity->id] ?? null;
                                $done = $m['done'] ?? 0;
                                $target = $m['target'] ?? 1;
                                $percent = $m['percent'] ?? 0;
                                $projected = $m['projected'] ?? 0;
                                $rangeLabel = $m['range_label'] ?? '';
                                $streak = $m['streak'] ?? 0;
                                $unit = $activity->unit ?: 'check';

                                $subjectLabel = null;
                                $subjectUrl = null;

                                if ($activity->subject_type === \App\Models\Wallet::class) {
                                    $subjectLabel = (($activity->subject?->name) ?: ('#' . $activity->subject_id));
                                    $subjectUrl = route('wallets.index', ['wallet_id' => $activity->subject_id]);
                                } elseif ($activity->subject_type === \App\Models\Budget::class) {
                                    $budgetCat = $activity->subject?->category?->name;
                                    $subjectLabel = ($budgetCat ? $budgetCat : ('#' . $activity->subject_id));
                                    $subjectUrl = route('budgets.index', ['budget_id' => $activity->subject_id]);
                                } elseif ($activity->subject_type === \App\Models\ShoppingList::class) {
                                    $subjectLabel = (($activity->subject?->name) ?: ('#' . $activity->subject_id));
                                    if ($activity->subject_id) {
                                        $subjectUrl = route('food.shopping-list.show', ['list' => $activity->subject_id]);
                                    }
                                }

                                $scopeLabel = $activity->family_group_id ? 'Compartido' : 'Personal';
                            @endphp

                            <tr class="text-sm text-gray-800 dark:text-gray-100">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold truncate">{{ $activity->title }}</span>
                                                @if(!$activity->is_active)
                                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">Inactivo</span>
                                                @endif
                                            </div>
                                            @if($activity->description)
                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 truncate">{{ $activity->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                    <div class="font-semibold">{{ $activity->kind === 'task' ? 'Tarea' : 'Hábito' }}</div>
                                    <div>{{ $rangeLabel }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">{{ $scopeLabel }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-xs text-gray-600 dark:text-gray-300">
                                        <span class="font-semibold">{{ rtrim(rtrim(number_format($done, 3, '.', ''), '0'), '.') }}</span>
                                        / {{ $target }} {{ $unit }}
                                        <span class="ml-2 font-semibold">{{ $percent }}%</span>
                                    </div>
                                    <div class="mt-1 h-2 w-40 rounded-full bg-gray-100 dark:bg-gray-800">
                                        <div class="h-2 rounded-full bg-indigo-600" style="width: {{ min(100, $percent) }}%"></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                    {{ rtrim(rtrim(number_format($projected, 2, '.', ''), '0'), '.') }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                    {{ $streak > 0 ? ($streak . ' días') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    @if($subjectLabel)
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">{{ $subjectLabel }}</div>
                                        @if($subjectUrl)
                                            <a href="{{ $subjectUrl }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-200">Ir</a>
                                        @endif
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                    @dateCo($activity->due_on)
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center justify-end gap-3">
                                        <button
                                            type="button"
                                            wire:click="checkIn({{ $activity->id }})"
                                            @disabled(!$activity->is_active)
                                            class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                                        >
                                            +1
                                        </button>
                                        <button type="button" wire:click="openForm({{ $activity->id }})" class="text-xs font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                            Editar
                                        </button>
                                        <button type="button" wire:click="toggleActive({{ $activity->id }})" class="text-xs font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                            {{ $activity->is_active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Aún no tienes hábitos o tareas. Crea el primero.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @forelse ($activities as $activity)
                    @php
                        $m = $metrics[$activity->id] ?? null;
                        $done = $m['done'] ?? 0;
                        $target = $m['target'] ?? 1;
                        $percent = $m['percent'] ?? 0;
                        $projected = $m['projected'] ?? 0;
                        $rangeLabel = $m['range_label'] ?? '';
                        $streak = $m['streak'] ?? 0;
                        $unit = $activity->unit ?: 'check';

                        $subjectLabel = null;
                        $subjectUrl = null;

                        if ($activity->subject_type === \App\Models\Wallet::class) {
                            $subjectLabel = 'Billetera: ' . (($activity->subject?->name) ?: ('#' . $activity->subject_id));
                            $subjectUrl = route('wallets.index', ['wallet_id' => $activity->subject_id]);
                        } elseif ($activity->subject_type === \App\Models\Budget::class) {
                            $budgetCat = $activity->subject?->category?->name;
                            $subjectLabel = 'Presupuesto: ' . ($budgetCat ? $budgetCat : ('#' . $activity->subject_id));
                            $subjectUrl = route('budgets.index', ['budget_id' => $activity->subject_id]);
                        } elseif ($activity->subject_type === \App\Models\ShoppingList::class) {
                            $subjectLabel = 'Lista: ' . (($activity->subject?->name) ?: ('#' . $activity->subject_id));
                            if ($activity->subject_id) {
                                $subjectUrl = route('food.shopping-list.show', ['list' => $activity->subject_id]);
                            }
                        }
                    @endphp

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="truncate text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $activity->title }}</h2>
                                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200">
                                        {{ $activity->kind === 'task' ? 'Tarea' : 'Hábito' }} · {{ $rangeLabel }}
                                    </span>
                                    @if($subjectLabel)
                                        <span class="rounded-full bg-slate-50 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                            {{ $subjectLabel }}
                                        </span>
                                    @endif
                                    @if(!$activity->is_active)
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">Inactivo</span>
                                    @endif
                                </div>

                                @if($activity->description)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $activity->description }}</p>
                                @endif

                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                                        <span>Progreso: <span class="font-semibold">{{ rtrim(rtrim(number_format($done, 3, '.', ''), '0'), '.') }}</span> / {{ $target }} {{ $unit }}</span>
                                        <span class="font-semibold">{{ $percent }}%</span>
                                    </div>
                                    <div class="mt-2 h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800">
                                        <div class="h-2 rounded-full bg-indigo-600" style="width: {{ min(100, $percent) }}%"></div>
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-600 dark:text-gray-400">
                                        <span class="rounded-full bg-gray-50 px-2 py-0.5 dark:bg-gray-800">Proyección: {{ rtrim(rtrim(number_format($projected, 2, '.', ''), '0'), '.') }}</span>
                                        @if($streak > 0)
                                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">Racha: {{ $streak }} días</span>
                                        @endif
                                        @if($activity->due_on)
                                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200">Vence: @dateCo($activity->due_on)</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-col gap-2">
                                <button
                                    type="button"
                                    wire:click="checkIn({{ $activity->id }})"
                                    @disabled(!$activity->is_active)
                                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    +1
                                </button>
                                @if($subjectUrl)
                                    <a href="{{ $subjectUrl }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-200">
                                        Ir
                                    </a>
                                @endif
                                <button type="button" wire:click="openForm({{ $activity->id }})" class="text-sm font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                    Editar
                                </button>
                                <button type="button" wire:click="toggleActive({{ $activity->id }})" class="text-sm font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                    {{ $activity->is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-2 rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        Aún no tienes hábitos o tareas. Crea el primero.
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
