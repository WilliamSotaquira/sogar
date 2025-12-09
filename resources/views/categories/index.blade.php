<x-layouts.app :title="__('Categorías')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-8 shadow-lg dark:from-emerald-600 dark:to-teal-700">
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
            <div class="flex flex-wrap gap-2">
                <span class="hero-chip text-xs">Colores y descripciones opcionales</span>
                <span class="hero-chip text-xs">Separación ingreso / gasto</span>
                <span class="hero-chip text-xs">Solo tú puedes editar tus categorías</span>
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

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
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
                            class="text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-300 dark:hover:text-amber-200"
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $editingCategory->name ?? '') }}"
                            class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="Ej. Arriendo, Freelance, Mercado"
                            required
                        >
                        @error('name')
                            <p class="text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                            <select
                                name="type"
                                class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 appearance-none"
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color</label>
                            <input
                                type="text"
                                name="color"
                                value="{{ old('color', $editingCategory->color ?? '') }}"
                                class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                placeholder="#0ea5e9 (opcional)"
                            >
                            @error('color')
                                <p class="text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción (opcional)</label>
                        <input
                            type="text"
                            name="description"
                            value="{{ old('description', $editingCategory->description ?? '') }}"
                            class="block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
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
                            class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800"
                            @checked(old('is_active', $editingCategory?->is_active ?? true))
                        >
                        Activa para usar en formularios
                    </label>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            {{ $editingCategory ? 'Guardar cambios' : 'Crear categoría' }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Listado</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $categories->count() }} categorías disponibles</p>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($categories as $category)
                            @php
                                $color = $category->color ?: ($category->type === 'income' ? '#10b981' : '#f97316');
                                $isOwner = $category->user_id === auth()->id();
                            @endphp
                            <div class="rounded-lg border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
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
                                    <div class="flex items-center gap-2">
                                        @if ($isOwner)
                                            <a
                                                href="{{ route('categories.index', ['edit' => $category->id]) }}"
                                                class="text-sm font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-300 dark:hover:text-amber-200"
                                            >
                                                Editar
                                            </a>
                                            <form
                                                method="POST"
                                                action="{{ route('categories.destroy', $category) }}"
                                                onsubmit="return confirm('¿Eliminar la categoría {{ $category->name }}?');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-sm font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-300 dark:hover:text-rose-200">
                                                    Eliminar
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
</x-layouts.app>
