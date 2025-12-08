<x-layouts.app :title="__('Nueva ubicación')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-1';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
    @endphp

    <div class="mx-auto w-full max-w-3xl space-y-6">
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-2 text-white">
                <p class="text-sm uppercase tracking-wide font-semibold">Inventario doméstico</p>
                <h1 class="text-3xl font-bold">Agregar ubicación</h1>
                <p class="text-sm text-white/80">Define espacios físicos para clasificar tus productos y lotes.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 dark:border-rose-900/40 dark:bg-rose-900/20 dark:text-rose-200">
                <p class="font-semibold text-sm mb-2">Revisa los siguientes campos:</p>
                <ul class="text-xs list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form method="POST" action="{{ route('food.locations.store') }}" class="space-y-6 p-6">
                @csrf

                <div>
                    <label for="name" class="{{ $label }}">Nombre de la ubicación *</label>
                    <input id="name" name="name" value="{{ old('name') }}" required class="{{ $input }}" placeholder="Ej. Nevera, Alacena, Baño" />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="color" class="{{ $label }}">Color identificador</label>
                        <input id="color" name="color" type="color" value="{{ old('color', '#22c55e') }}" class="mt-1 h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Se usa para resaltar la ubicación en el inventario.</p>
                    </div>
                    <div>
                        <label for="sort_order" class="{{ $label }}">Orden de visualización</label>
                        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', 0) }}" class="{{ $input }}" />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_default" name="is_default" value="1" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_default'))>
                    <label for="is_default" class="text-sm text-gray-700 dark:text-gray-300">Establecer como ubicación predeterminada</label>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('food.locations.index') }}" class="{{ $btnSecondary }}">Cancelar</a>
                    <button type="submit" class="{{ $btnPrimary }}">Guardar ubicación</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
