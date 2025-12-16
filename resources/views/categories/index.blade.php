<x-layouts.app :title="__('Categorías')">
    <div class="mx-auto w-full max-w-7xl space-y-6 px-3 sm:px-0">
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 shadow-lg sm:p-8 dark:from-emerald-600 dark:to-teal-700">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Ordena tus ingresos y gastos</p>
                    <h1 class="text-3xl font-bold">Categorías</h1>
                    <p class="text-sm text-white/80">Crea etiquetas propias, desactiva lo que no uses y simplifica presupuestos y transacciones.</p>
                </div>
                <div class="hero-chip text-sm font-semibold">
                    Curadas + personales
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="hero-chip text-xs">Colores y descripciones opcionales</span>
                <span class="hero-chip text-xs">Ingreso / gasto</span>
                <span class="hero-chip text-xs">Editas solo las tuyas</span>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 dark:border-rose-900/50 dark:bg-rose-900/30 dark:text-rose-100">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-12">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900 lg:col-span-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-50">
                            {{ $editingCategory ? 'Editar categoría' : 'Nueva categoría' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Dale un nombre claro y define si suma o resta.
                        </p>
                    </div>
                    @if ($editingCategory)
                        <a
                            href="{{ route('categories.index') }}"
                            class="text-xs font-semibold text-amber-600 hover:text-amber-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2 dark:text-amber-300 dark:hover:text-amber-200 dark:focus-visible:ring-offset-gray-900"
                        >
                            Crear nueva
                        </a>
                    @endif
                </div>

                <form
                    method="POST"
                    action="{{ $editingCategory ? route('categories.update', $editingCategory) : route('categories.store') }}"
                    class="mt-4 space-y-4"
                >
                    @csrf
                    @if ($editingCategory)
                        @method('PUT')
                    @endif

                    <div class="space-y-1">
                        <label for="category-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                        <input
                            id="category-name"
                            type="text"
                            name="name"
                            value="{{ old('name', $editingCategory->name ?? '') }}"
                            class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="Ej. Arriendo, Freelance, Mercado"
                            required
                            aria-required="true"
                        >
                        @error('name')
                            <p class="text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label for="category-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                            <select
                                id="category-type"
                                name="type"
                                class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none"
                                required
                            >
                                <option value="income" @selected(old('type', $editingCategory->type ?? 'expense') === 'income')>Ingreso</option>
                                <option value="expense" @selected(old('type', $editingCategory->type ?? 'expense') === 'expense')>Gasto</option>
                            </select>
                            @error('type')
                                <p class="text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1">
                            <label for="category-color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color</label>
                            <div class="flex items-center gap-2">
                                <input
                                    id="category-color-picker"
                                    type="color"
                                    value="{{ old('color', $editingCategory->color ?? '#10b981') ?: '#10b981' }}"
                                    class="h-12 w-12 rounded-xl border border-gray-200 bg-white p-1 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                                    aria-label="Selector de color"
                                >
                                <input
                                    id="category-color"
                                    type="text"
                                    name="color"
                                    value="{{ old('color', $editingCategory->color ?? '') }}"
                                    class="block h-12 min-w-0 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                    placeholder="#0ea5e9 (opcional)"
                                    inputmode="text"
                                >
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @php
                                    $presetColors = ['#10b981', '#0ea5e9', '#8b5cf6', '#f97316', '#ef4444', '#64748b'];
                                @endphp
                                @foreach ($presetColors as $preset)
                                    <button
                                        type="button"
                                        class="h-8 w-8 rounded-full border border-gray-200 shadow-sm ring-2 ring-white dark:border-gray-700 dark:ring-gray-900"
                                        style="background: {{ $preset }}"
                                        aria-label="Usar color {{ $preset }}"
                                        data-color-preset="{{ $preset }}"
                                    ></button>
                                @endforeach
                            </div>
                            @error('color')
                                <p class="text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="category-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción (opcional)</label>
                        <input
                            id="category-description"
                            type="text"
                            name="description"
                            value="{{ old('description', $editingCategory->description ?? '') }}"
                            class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="Ayuda a recordar cómo usarla"
                        >
                        @error('description')
                            <p class="text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800"
                            @checked(old('is_active', $editingCategory?->is_active ?? true))
                        >
                        Activa para usar en formularios
                    </label>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            {{ $editingCategory ? 'Guardar cambios' : 'Crear categoría' }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="space-y-4 lg:col-span-8">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Listado</h2>
                            <p id="categories-count" role="status" aria-live="polite" aria-atomic="true" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $categories->count() }} categorías disponibles</p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:items-center">
                        <div class="min-w-0 flex-1">
                            <label for="categories-search" class="sr-only">Buscar</label>
                            <input
                                id="categories-search"
                                type="search"
                                class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                placeholder="Buscar por nombre o descripción…"
                                autocomplete="off"
                            >
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap lg:flex-nowrap lg:justify-end">
                            <div class="min-w-0 sm:min-w-[180px]">
                                <label for="categories-type" class="sr-only">Tipo</label>
                                <select id="categories-type" class="h-12 w-full rounded-xl border border-gray-200 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none">
                                    <option value="all">Todos</option>
                                    <option value="income">Ingresos</option>
                                    <option value="expense">Gastos</option>
                                </select>
                            </div>
                            <div class="min-w-0 sm:min-w-[200px]">
                                <label for="categories-scope" class="sr-only">Origen</label>
                                <select id="categories-scope" class="h-12 w-full rounded-xl border border-gray-200 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none">
                                    <option value="all">Base + Personal</option>
                                    <option value="base">Base</option>
                                    <option value="personal">Personal</option>
                                </select>
                            </div>
                            <div class="min-w-0 sm:min-w-[210px]">
                                <label for="categories-status" class="sr-only">Estado</label>
                                <select id="categories-status" class="h-12 w-full rounded-xl border border-gray-200 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none">
                                    <option value="all">Activas + Inactivas</option>
                                    <option value="active">Activas</option>
                                    <option value="inactive">Inactivas</option>
                                </select>
                            </div>
                            <button id="categories-clear" type="button" class="h-12 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800 dark:focus-visible:ring-offset-gray-900">
                                Limpiar
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <p id="categories-empty" class="hidden rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300" role="status" aria-live="polite">
                            No hay categorías que coincidan con los filtros.
                        </p>
                        @forelse ($categories as $category)
                            @php
                                $color = $category->color ?: ($category->type === 'income' ? '#10b981' : '#f97316');
                                $isOwner = $category->user_id === auth()->id();
                            @endphp
                            <div
                                class="category-card rounded-lg border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                                data-name="{{ strtolower($category->name) }}"
                                data-desc="{{ strtolower($category->description ?? '') }}"
                                data-type="{{ $category->type }}"
                                data-scope="{{ $isOwner ? 'personal' : 'base' }}"
                                data-active="{{ $category->is_active ? 'active' : 'inactive' }}"
                            >
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="mt-0.5 h-10 w-10 shrink-0 rounded-xl border border-white/60 shadow-inner ring-4 ring-white dark:border-gray-700 dark:ring-gray-800"
                                            style="background: {{ $color }}"
                                            aria-hidden="true"
                                        ></span>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $category->name }}</p>
                                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $category->type === 'income' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' }}">
                                                    {{ $category->type === 'income' ? 'Ingreso' : 'Gasto' }}
                                                </span>
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                    {{ $isOwner ? 'Personal' : 'Base' }}
                                                </span>
                                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $category->is_active ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                                                    {{ $category->is_active ? 'Activa' : 'Inactiva' }}
                                                </span>
                                            </div>
                                            @if ($category->description)
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $category->description }}</p>
                                            @endif
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                Creada {{ $category->created_at?->diffForHumans() ?? 'sin fecha' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 sm:shrink-0 sm:justify-end">
                                        @if ($isOwner)
                                            <a
                                                href="{{ route('categories.index', ['edit' => $category->id]) }}"
                                                class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 dark:text-emerald-300 dark:hover:text-emerald-200 dark:focus-visible:ring-offset-gray-900"
                                            >
                                                Editar
                                            </a>
                                            <form
                                                method="POST"
                                                action="{{ route('categories.destroy', $category) }}"
                                                class="inline-flex items-center gap-2"
                                                data-inline-confirm
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <span class="sr-only" role="status" aria-live="polite" data-inline-confirm-status></span>
                                                <button
                                                    type="button"
                                                    class="text-sm font-semibold text-rose-600 hover:text-rose-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 focus-visible:ring-offset-2 dark:text-rose-300 dark:hover:text-rose-200 dark:focus-visible:ring-offset-gray-900"
                                                    data-inline-confirm-arm
                                                >
                                                    Eliminar
                                                </button>
                                                <button
                                                    type="submit"
                                                    class="hidden text-sm font-semibold text-rose-700 hover:text-rose-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 focus-visible:ring-offset-2 dark:text-rose-200 dark:hover:text-rose-100 dark:focus-visible:ring-offset-gray-900"
                                                    data-inline-confirm-confirm
                                                >
                                                    Confirmar
                                                </button>
                                                <button
                                                    type="button"
                                                    class="hidden text-sm font-semibold text-gray-600 hover:text-gray-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2 dark:text-gray-300 dark:hover:text-gray-100 dark:focus-visible:ring-offset-gray-900"
                                                    data-inline-confirm-cancel
                                                >
                                                    Cancelar
                                                </button>
                                            </form>
                                        @else
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Solo lectura</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Aún no tienes categorías personalizadas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Color UX: picker <-> text + presets
                const picker = document.getElementById('category-color-picker');
                const colorInput = document.getElementById('category-color');
                const applyColor = (hex) => {
                    if (!hex) return;
                    if (picker) picker.value = hex;
                    if (colorInput) colorInput.value = hex;
                };
                if (colorInput && picker) {
                    const initial = colorInput.value.trim();
                    if (/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/.test(initial)) {
                        picker.value = initial.slice(0, 7);
                    }
                }
                picker?.addEventListener('input', () => applyColor(picker.value));
                colorInput?.addEventListener('input', () => {
                    const v = colorInput.value.trim();
                    if (/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/.test(v)) {
                        if (picker) picker.value = v.slice(0, 7);
                    }
                });
                document.querySelectorAll('[data-color-preset]').forEach((btn) => {
                    btn.addEventListener('click', () => applyColor(btn.dataset.colorPreset));
                });

                // Filters (client-side)
                const cards = Array.from(document.querySelectorAll('.category-card'));
                const searchEl = document.getElementById('categories-search');
                const typeEl = document.getElementById('categories-type');
                const scopeEl = document.getElementById('categories-scope');
                const statusEl = document.getElementById('categories-status');
                const clearEl = document.getElementById('categories-clear');
                const countEl = document.getElementById('categories-count');
                const emptyEl = document.getElementById('categories-empty');

                const applyFilters = () => {
                    const q = (searchEl?.value || '').trim().toLowerCase();
                    const type = typeEl?.value || 'all';
                    const scope = scopeEl?.value || 'all';
                    const status = statusEl?.value || 'all';

                    let visible = 0;
                    cards.forEach((card) => {
                        const hay = `${card.dataset.name || ''} ${card.dataset.desc || ''}`;
                        const okQ = !q || hay.includes(q);
                        const okType = type === 'all' || card.dataset.type === type;
                        const okScope = scope === 'all' || card.dataset.scope === scope;
                        const okStatus = status === 'all' || card.dataset.active === status;
                        const show = okQ && okType && okScope && okStatus;
                        card.classList.toggle('hidden', !show);
                        if (show) visible++;
                    });

                    if (countEl) {
                        countEl.textContent = `${visible} categorías visibles`;
                    }
                    if (emptyEl) {
                        emptyEl.classList.toggle('hidden', visible !== 0);
                    }
                };

                const clearFilters = () => {
                    if (searchEl) searchEl.value = '';
                    if (typeEl) typeEl.value = 'all';
                    if (scopeEl) scopeEl.value = 'all';
                    if (statusEl) statusEl.value = 'all';
                    applyFilters();
                };

                searchEl?.addEventListener('input', applyFilters);
                typeEl?.addEventListener('change', applyFilters);
                scopeEl?.addEventListener('change', applyFilters);
                statusEl?.addEventListener('change', applyFilters);
                clearEl?.addEventListener('click', clearFilters);

                applyFilters();
            });
        </script>
    @endpush
</x-layouts.app>
