<x-layouts.app :title="__('Editar Producto')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $textarea = 'mt-1 block w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
        $imageSrc = $product->image_url ?? $product->image_path;
        $formatInt = fn ($value) => is_null($value) ? null : (int) $value;
    @endphp

    <div class="mx-auto w-full max-w-4xl space-y-6">
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-4 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Editar producto</p>
                    <h1 class="text-3xl font-bold">{{ $product->name }}</h1>
                    <p class="text-sm text-white/80">{{ $product->brand ?: 'Sin marca' }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('food.products.show', $product) }}" class="{{ $btnSecondary }} bg-white text-emerald-600 border-white">Ver detalle</a>
                    <a href="{{ route('food.products.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">Listado</a>
                </div>
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

        <div class="rounded-xl border border-gray-200 bg-white shadow-md dark:border-gray-800 dark:bg-gray-900">
            <form method="POST" action="{{ route('food.products.update', $product) }}" class="divide-y divide-gray-200 dark:divide-gray-800">
                @csrf
                @method('PUT')

                <section class="p-6 space-y-6">
                    <header>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Identificación del producto</p>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Datos para localizar y clasificar el artículo</h2>
                    </header>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="name" class="{{ $label }}">Nombre</label>
                            <input id="name" name="name" value="{{ old('name', $product->name) }}" required class="{{ $input }}" />
                        </div>
                        <div>
                            <label for="brand" class="{{ $label }}">Marca</label>
                            <input id="brand" name="brand" value="{{ old('brand', $product->brand) }}" class="{{ $input }}" />
                        </div>
                        <div>
                            <label for="barcode" class="{{ $label }}">Código de barras</label>
                            <input id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="{{ $input }}" />
                        </div>
                        <div>
                            <label for="type_id" class="{{ $label }}">Tipo</label>
                            <select id="type_id" name="type_id" class="{{ $input }}">
                                <option value="">Selecciona</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" @selected(old('type_id', $product->type_id) == $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="default_location_id" class="{{ $label }}">Ubicación por defecto</label>
                            <select id="default_location_id" name="default_location_id" class="{{ $input }}">
                                <option value="">Selecciona</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" @selected(old('default_location_id', $product->default_location_id) == $loc->id)>{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                <section class="p-6 space-y-6">
                    <header>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Inventario y control</p>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Parámetros usados para lotes y compras</h2>
                    </header>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="shelf_life_days" class="{{ $label }}">Vida útil (días)</label>
                            <input id="shelf_life_days" name="shelf_life_days" type="number" min="1" max="3650" value="{{ old('shelf_life_days', $formatInt($product->shelf_life_days)) }}" class="{{ $input }}" />
                        </div>
                        <div>
                            <label for="min_stock_qty" class="{{ $label }}">Stock mínimo</label>
                            <input id="min_stock_qty" name="min_stock_qty" type="number" step="0.1" min="0" value="{{ old('min_stock_qty', $formatInt($product->min_stock_qty)) }}" class="{{ $input }}" />
                        </div>
                        <div>
                            <label for="unit_size" class="{{ $label }}">Cantidad por unidad</label>
                            <input id="unit_size" name="unit_size" type="number" step="0.001" min="0.001" value="{{ old('unit_size', $formatInt($product->unit_size)) }}" class="{{ $input }}" />
                        </div>
                        <div>
                            <label for="unit_base" class="{{ $label }}">Unidad base</label>
                            <select id="unit_base" name="unit_base" class="{{ $input }}">
                                <option value="unit" @selected(old('unit_base', $product->unit_base) === 'unit')>Unidad</option>
                                <option value="g" @selected(old('unit_base', $product->unit_base) === 'g')>Gramos (g)</option>
                                <option value="kg" @selected(old('unit_base', $product->unit_base) === 'kg')>Kilogramos (kg)</option>
                                <option value="ml" @selected(old('unit_base', $product->unit_base) === 'ml')>Mililitros (ml)</option>
                                <option value="l" @selected(old('unit_base', $product->unit_base) === 'l')>Litros (L)</option>
                            </select>
                        </div>
                        <div>
                            <label for="presentation_qty" class="{{ $label }}">Tamaño de porción / presentación</label>
                            <input id="presentation_qty" name="presentation_qty" type="number" step="0.001" min="0" value="{{ old('presentation_qty', $formatInt($product->presentation_qty)) }}" class="{{ $input }}" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Este valor ayuda a calcular sugerencias de compra.</p>
                        </div>
                        <div class="flex items-center gap-3 pt-6">
                            <input type="checkbox" id="is_active" name="is_active" value="1" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_active', $product->is_active))>
                            <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Producto activo</label>
                        </div>
                    </div>
                </section>

                <section class="p-6 space-y-6">
                    <header>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Notas e imagen</p>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Información de referencia para compras y ubicación</h2>
                    </header>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="{{ $label }}">Vista miniatura</label>
                            <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-center p-3 min-h-[190px]">
                                @if($imageSrc)
                                    <a href="{{ $imageSrc }}" target="_blank" rel="noopener noreferrer" class="block cursor-zoom-in focus-visible:outline-none focus-visible:ring focus-visible:ring-emerald-500 rounded-lg" title="Ver imagen en tamaño completo">
                                        <img src="{{ $imageSrc }}" alt="{{ $product->name }}" class="max-h-[250px] max-w-[250px] w-full object-contain rounded-lg mx-auto">
                                    </a>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Sin imagen disponible</span>
                                @endif
                            </div>
                            @if($imageSrc)
                                <p class="text-xs text-gray-500 dark:text-gray-400">La vista previa no supera los 250 px. Haz clic para abrir la imagen original.</p>
                            @endif
                        </div>
                        <div>
                            <label for="notes" class="{{ $label }}">Notas</label>
                            <textarea id="notes" name="notes" rows="6" class="{{ $textarea }}">{{ old('notes', $product->notes) }}</textarea>
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3 p-6">
                    <a href="{{ route('food.products.show', $product) }}" class="{{ $btnSecondary }}">Cancelar</a>
                    <button type="submit" class="{{ $btnPrimary }}">✓ Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
