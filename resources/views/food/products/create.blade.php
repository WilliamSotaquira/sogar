<x-layouts.app :title="__('Nuevo Producto de Alimentos')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $textarea = 'mt-1 block w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
    @endphp

    <div class="mx-auto w-full max-w-4xl space-y-6">
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-8 shadow-lg dark:from-emerald-600 dark:to-teal-700">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Inventario doméstico</p>
                    <h1 class="text-3xl font-bold">Agregar Nuevo Producto</h1>
                    <p class="text-sm text-white/80">Escanea su código o completa los datos manualmente para poder controlarlo en inventario.</p>
                </div>
                <a href="{{ route('food.products.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                    ← Volver al listado
                </a>
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

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Datos del producto</h2>
                <p class="text-sm text-gray-500 mt-1">El sistema intentará autocompletar usando tu inventario y OpenFoodFacts.</p>
            </div>

            <form id="product-form" method="POST" action="{{ route('food.products.store') }}" class="p-6 space-y-6" aria-label="Formulario para agregar un nuevo producto">
                @csrf
                <div class="lg:grid lg:grid-cols-12 gap-6">
                    <div class="space-y-6 lg:col-span-8">
                        <section class="rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-4 md:p-5 space-y-4">
                            <header class="flex flex-col gap-1">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Identificación</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Estos datos nos ayudan a localizar y sugerir el producto.</p>
                            </header>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <label class="{{ $label }}">Código de barras</label>
                                        <button type="button" id="scan-barcode" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            Escanear código
                                        </button>
                                        <button type="button" id="test-barcode" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 transition">
                                            🧪 Test
                                        </button>
                                    </div>
                                    <input id="barcode-input" name="barcode" value="{{ old('barcode') }}" class="{{ $input }}" placeholder="Escanea o escribe el código" autocomplete="off" aria-describedby="barcode-helper" />
                                    <p id="barcode-helper" class="text-xs text-gray-500 dark:text-gray-400 mt-1">Un código válido autocompleta la información del producto</p>
                                </div>
                                <div>
                                    <label class="{{ $label }}">Nombre del producto *</label>
                                    <input name="name" value="{{ old('name') }}" required class="{{ $input }}" placeholder="Ej: Leche entera 1L" />
                                </div>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="{{ $label }}">Marca</label>
                                    <input name="brand" value="{{ old('brand') }}" class="{{ $input }}" placeholder="Ej: Lala" />
                                </div>
                                <div>
                                    <label class="{{ $label }}">Tipo</label>
                                    <select name="type_id" class="{{ $input }}">
                                        <option value="">Selecciona</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id }}" @selected(old('type_id') == $type->id)>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-4 md:p-5 space-y-4">
                            <header>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Inventario y control</h3>
                            </header>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="{{ $label }}">Ubicación por defecto</label>
                                    <select name="default_location_id" class="{{ $input }}">
                                        <option value="">Selecciona</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc->id }}" @selected(old('default_location_id') == $loc->id)>{{ $loc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="{{ $label }}">Vida útil (días)</label>
                                    <input name="shelf_life_days" type="number" min="1" max="3650" value="{{ old('shelf_life_days') }}" class="{{ $input }}" placeholder="Ej: 30" />
                                </div>
                                <div>
                                    <label class="{{ $label }}">Stock mínimo</label>
                                    <input name="min_stock_qty" type="number" step="0.1" min="0" value="{{ old('min_stock_qty', 1) }}" class="{{ $input }}" />
                                </div>
                                <div>
                                    <label class="{{ $label }}">Tamaño del producto</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <input name="unit_size" type="number" step="0.001" min="0.001" value="{{ old('unit_size', 1) }}" class="{{ $input }}" />
                                        <select name="unit_base" class="{{ $input }}">
                                            <option value="unit" @selected(old('unit_base') === 'unit')>Unidad</option>
                                            <option value="g" @selected(old('unit_base') === 'g')>Gramos (g)</option>
                                            <option value="kg" @selected(old('unit_base') === 'kg')>Kilogramos (kg)</option>
                                            <option value="ml" @selected(old('unit_base') === 'ml')>Mililitros (ml)</option>
                                            <option value="l" @selected(old('unit_base') === 'l')>Litros (L)</option>
                                        </select>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Ej: 500 g, 1 L, 12 unidades</p>
                                </div>
                            </div>
                            <div>
                                <label class="{{ $label }}">Tamaño porción (opcional)</label>
                                <input id="portion-input" name="presentation_qty" type="number" step="0.001" min="0" value="{{ old('presentation_qty') }}" class="{{ $input }}" placeholder="Ej: 24" />
                                <p id="portion-hint" class="text-xs text-emerald-600 dark:text-emerald-300 mt-1 hidden"></p>
                            </div>
                        </section>

                        <section class="rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-4 md:p-5 space-y-4">
                            <header>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Notas y compra</h3>
                            </header>
                            <div class="space-y-4">
                                <div>
                                    <label class="{{ $label }}">Descripción / Notas</label>
                                    <textarea name="description" rows="3" class="{{ $textarea }}" placeholder="Detalles útiles para identificar este producto">{{ old('description') }}</textarea>
                                </div>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="{{ $label }}">Precio inicial</label>
                                        <input name="initial_price" type="number" min="0" step="0.01" value="{{ old('initial_price') }}" class="{{ $input }}" placeholder="0.00" />
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">Tienda / Vendor</label>
                                        <input name="initial_vendor" value="{{ old('initial_vendor') }}" class="{{ $input }}" placeholder="Ej: Walmart" />
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="lg:col-span-4 space-y-4">
                        <div class="rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-5 space-y-4">
                            <header>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Imagen del producto</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Usamos la imagen de OpenFoodFacts si está disponible. Puedes reemplazarla pegando tu propia URL.</p>
                            </header>
                            <div class="flex gap-2">
                                <input type="url" name="image_url" id="image_url_field" value="{{ old('image_url') }}" class="{{ $input }} flex-1" placeholder="https://..." aria-describedby="image-url-helper" />
                                <a id="image-link" href="{{ old('image_url') }}" target="_blank" class="inline-flex items-center justify-center rounded-xl px-3 text-sm font-semibold text-emerald-600 hover:text-emerald-700 {{ old('image_url') ? '' : 'hidden' }}">Ver</a>
                            </div>
                            <p id="image-url-helper" class="sr-only">Escribe o pega una URL de imagen. Se mostrará una vista previa debajo.</p>
                                <div class="flex justify-center">
                                <div id="image-preview" class="h-36 w-full rounded-xl border border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 overflow-hidden cursor-zoom-in" style="max-width: 250px;">
                                    <a href="#" id="image-preview-link" target="_blank" class="hidden"></a>
                                    <span id="image-preview-placeholder">Sin imagen seleccionada</span>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg border border-dashed border-gray-200 dark:border-gray-800 p-4 text-sm text-gray-600 dark:text-gray-400 space-y-2">
                            <p class="font-semibold text-gray-900 dark:text-gray-100">Consejos rápidos</p>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Los campos con * son obligatorios.</li>
                                <li>Si escaneas un código, completaremos nombre y marca automáticamente.</li>
                                <li>El tamaño de porción ayuda a calcular sugerencias de compra.</li>
                            </ul>
                        </div>
                    </aside>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('food.products.index') }}" class="{{ $btnSecondary }}">Cancelar</a>
                    <button type="submit" class="{{ $btnPrimary }}">✓ Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trigger = document.getElementById('scan-barcode');
            const barcodeInput = document.getElementById('barcode-input');
            const nameInput = document.querySelector('input[name="name"]');
            const brandInput = document.querySelector('input[name="brand"]');
            const typeSelect = document.querySelector('select[name="type_id"]');
            const locationSelect = document.querySelector('select[name="default_location_id"]');
            const unitBaseInput = document.querySelector('[name="unit_base"]');
            const unitSizeInput = document.querySelector('input[name="unit_size"]');
            const minStockInput = document.querySelector('input[name="min_stock_qty"]');
            const shelfLifeInput = document.querySelector('input[name="shelf_life_days"]');
            const descriptionInput = document.querySelector('textarea[name="description"]');
            const portionHint = document.getElementById('portion-hint');
            const portionInput = document.getElementById('portion-input');
            const imageInput = document.getElementById('image_url_field');
            const imagePreview = document.getElementById('image-preview');
            const imageLink = document.getElementById('image-link');
            const imagePreviewLink = document.getElementById('image-preview-link');
            const imagePreviewPlaceholder = document.getElementById('image-preview-placeholder');
            let currentImageUrl = '';
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            let debounceId = null;
            let barcodeScanner = null;

            const updatePortionHint = (text, qty, unit) => {
                if (!portionHint) return;
                const value = (text || '').trim();
                let display = value;
                if (!display && qty) {
                    display = `${qty}${unit ? ` ${unit}` : ''}`;
                }
                if (display) {
                    portionHint.textContent = `Porción sugerida: ${display}`;
                    portionHint.classList.remove('hidden');
                } else {
                    portionHint.textContent = '';
                    portionHint.classList.add('hidden');
                }
            };

            const updateImagePreview = (url) => {
                if (!imagePreview) return;
                if (url) {
                    if (imagePreviewPlaceholder) {
                        imagePreviewPlaceholder.classList.add('hidden');
                    }
                    if (imagePreviewLink) {
                        imagePreviewLink.href = url;
                        imagePreviewLink.classList.remove('hidden');
                        imagePreviewLink.innerHTML = `<img src="${url}" alt="Vista previa" class="w-full h-full object-contain rounded-xl">`;
                    }
                    if (imageLink) {
                        imageLink.href = url;
                        imageLink.classList.remove('hidden');
                    }
                    currentImageUrl = url;
                    imagePreview?.classList.add('cursor-zoom-in');
                } else {
                    if (imagePreviewLink) {
                        imagePreviewLink.classList.add('hidden');
                        imagePreviewLink.innerHTML = '';
                    }
                    if (imagePreviewPlaceholder) {
                        imagePreviewPlaceholder.classList.remove('hidden');
                        imagePreviewPlaceholder.textContent = 'Sin imagen seleccionada';
                    }
                    if (imageLink) {
                        imageLink.href = '#';
                        imageLink.classList.add('hidden');
                    }
                    currentImageUrl = '';
                    imagePreview?.classList.remove('cursor-zoom-in');
                }
            };

            if (imageInput?.value) {
                updateImagePreview(imageInput.value);
            }

            imageInput?.addEventListener('input', (e) => updateImagePreview(e.target.value));
            imagePreview?.addEventListener('click', () => {
                if (currentImageUrl) {
                    window.open(currentImageUrl, '_blank', 'noopener,noreferrer');
                }
            });

            if (portionInput?.value) {
                updatePortionHint('', portionInput.value, unitBaseInput?.value);
            }

            const fillFromProduct = (product) => {
                if (!product) {
                    console.log('fillFromProduct: No hay producto para llenar');
                    return;
                }

                console.log('fillFromProduct: Llenando formulario con:', product);

                if (product.name && nameInput && !nameInput.value) {
                    nameInput.value = product.name;
                    console.log('Nombre llenado:', product.name);
                }
                if (product.brand && brandInput && !brandInput.value) {
                    brandInput.value = product.brand;
                    console.log('Marca llenada:', product.brand);
                }
                if (product.type_id && typeSelect) {
                    typeSelect.value = product.type_id;
                    console.log('Tipo llenado:', product.type_id);
                }
                if (product.default_location_id && locationSelect) {
                    locationSelect.value = product.default_location_id;
                    console.log('Ubicación llenada:', product.default_location_id);
                }
                if (product.unit_base && unitBaseInput) {
                    unitBaseInput.value = product.unit_base;
                    console.log('Unidad base llenada:', product.unit_base);
                }
                if (product.unit_size && unitSizeInput) {
                    unitSizeInput.value = product.unit_size;
                    console.log('Tamaño llenado:', product.unit_size);
                }
                if (product.min_stock_qty && minStockInput) {
                    minStockInput.value = product.min_stock_qty;
                    console.log('Stock mínimo llenado:', product.min_stock_qty);
                }
                if (product.shelf_life_days && shelfLifeInput) {
                    shelfLifeInput.value = product.shelf_life_days;
                    console.log('Vida útil llenada:', product.shelf_life_days);
                }
                if (product.description && descriptionInput && !descriptionInput.value) {
                    descriptionInput.value = product.description;
                    console.log('Descripción llenada:', product.description);
                }
                if (imageInput && product.image_url) {
                    imageInput.value = product.image_url;
                    updateImagePreview(product.image_url);
                    console.log('Imagen llenada:', product.image_url);
                }
                if (portionInput && product.presentation_qty) {
                    portionInput.value = product.presentation_qty;
                    updatePortionHint(product.portion_text || '', product.presentation_qty, product.unit_base);
                } else if (portionInput && product.portion_qty) {
                    portionInput.value = product.portion_qty;
                    updatePortionHint(product.portion_text || '', product.portion_qty, product.portion_unit || product.unit_base);
                }

                console.log('fillFromProduct: Formulario completado');
            };

            const parseQuantityText = (text) => {
                if (!text) return null;
                const sanitized = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
                const matches = [...sanitized.matchAll(/([\d.,]+)\s*(kg|kilo|kilogramos?|g|gramos?|ml|mililitros?|l|lt|litros?)/gi)];
                if (!matches.length) return null;
                const match = matches[matches.length - 1];
                let value = parseFloat(match[1].replace(',', '.'));
                if (!isFinite(value) || value <= 0) return null;
                const unitRaw = match[2];
                if (/^kg|kilo/.test(unitRaw)) {
                    return { unit: 'g', value: value * 1000 };
                }
                if (/^g|gram/.test(unitRaw)) {
                    return { unit: 'g', value };
                }
                if (/^ml|mili/.test(unitRaw)) {
                    return { unit: 'ml', value };
                }
                if (/^l|lt|lit/.test(unitRaw)) {
                    return { unit: 'ml', value: value * 1000 };
                }
                return { unit: 'unit', value };
            };

            const inferShelfLifeFromCategories = (categoriesText = '') => {
                const normalized = categoriesText.toLowerCase();
                const rules = [
                    { days: 7, keywords: ['fresh', 'dairy', 'lacteo', 'leche', 'yogur'] },
                    { days: 3, keywords: ['meat', 'carne', 'fish', 'pescado'] },
                    { days: 5, keywords: ['vegetable', 'verdura', 'fruta', 'fruit'] },
                    { days: 3, keywords: ['bread', 'pan', 'bakery'] },
                    { days: 365, keywords: ['canned', 'conserva', 'enlatado'] },
                    { days: 180, keywords: ['pasta', 'rice', 'arroz', 'cereal', 'grain'] },
                ];
                for (const rule of rules) {
                    if (rule.keywords.some(keyword => normalized.includes(keyword))) {
                        return rule.days;
                    }
                }
                return null;
            };

            const resolveBrandFromProduct = (product) => {
                if (Array.isArray(product.brands_tags) && product.brands_tags.length) {
                    return product.brands_tags[0];
                }
                if (product.brands) {
                    return product.brands.split(',')[0]?.trim() ?? '';
                }
                return product.brand_owner || '';
            };

            const fillFromOpenFoodFacts = async (code) => {
                console.log('fillFromOpenFoodFacts: Consultando código:', code);

                try {
                    const url = `https://world.openfoodfacts.org/api/v0/product/${encodeURIComponent(code)}.json`;
                    console.log('fillFromOpenFoodFacts: URL:', url);

                    const res = await fetch(url);
                    const data = await res.json();

                    console.log('fillFromOpenFoodFacts: Respuesta recibida:', data);

                    if (data?.status === 1 && data.product) {
                        console.log('fillFromOpenFoodFacts: Producto encontrado en OpenFoodFacts');

                        const p = data.product;
                        const name = p.product_name_es || p.product_name || p.generic_name_es || p.generic_name || '';
                        const brand = resolveBrandFromProduct(p);
                        const packagingText = p.product_quantity
                            ? `${p.product_quantity} ${p.product_quantity_unit || ''}`.trim()
                            : (p.quantity || '');
                        const servingText = p.serving_size || '';
                        const packQty = parseQuantityText(packagingText);
                        const servingQty = parseQuantityText(servingText);
                        const categoriesText = p.categories || (Array.isArray(p.categories_tags) ? p.categories_tags.join(',') : '');
                        const shelfLife = inferShelfLifeFromCategories(categoriesText);
                        const imageUrl = p.image_front_url || p.image_url || null;

                        console.log('fillFromOpenFoodFacts: Datos procesados:', {
                            name, brand, packQty, servingQty, shelfLife, imageUrl
                        });

                        if (imageUrl) {
                            updateImagePreview(imageUrl);
                            if (imageInput) {
                                imageInput.value = imageUrl;
                            }
                        }

                        console.log('fillFromOpenFoodFacts: Llamando a fillFromProduct...');

                        fillFromProduct({
                            name,
                            brand,
                            unit_base: packQty?.unit || undefined,
                            unit_size: packQty?.value || undefined,
                            shelf_life_days: shelfLife || undefined,
                            description: p.generic_name_es || p.generic_name || '',
                        });

                        if (portionInput && servingQty?.value) {
                            portionInput.value = servingQty.value;
                        }
                        updatePortionHint(servingText, servingQty?.value, servingQty?.unit);

                        if (imageUrl && imageInput) {
                            imageInput.value = imageUrl;
                        }

                        console.log('✅ Producto autocompletado exitosamente desde OpenFoodFacts');
                        return true;
                    } else {
                        console.log('fillFromOpenFoodFacts: Producto no encontrado (status:', data?.status, ')');
                    }
                } catch (err) {
                    console.error('fillFromOpenFoodFacts: Error:', err);
                }
                updatePortionHint('');
                return false;
            };

            const lookupBarcode = async (code) => {
                if (!code) return;
                console.log('lookupBarcode: Buscando código:', code);

                try {
                    console.log('lookupBarcode: Enviando petición a /food/scan');
                    const res = await fetch('/food/scan', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf || '',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ code }),
                    });

                    console.log('lookupBarcode: Respuesta recibida, status:', res.status);

                    if (res.ok) {
                        const data = await res.json();
                        console.log('lookupBarcode: Datos recibidos completos:', data);

                        // El endpoint puede devolver found:false si no encuentra nada
                        if (data.found === false) {
                            console.log('Producto no encontrado, consultando OpenFoodFacts...');
                            const filled = await fillFromOpenFoodFacts(code);
                            if (!filled) {
                                console.log('Código no encontrado en OpenFoodFacts');
                                if (portionInput) {
                                    portionInput.value = '';
                                    updatePortionHint('');
                                }
                            }
                            return;
                        }

                        // Si viene con producto, llenar formulario
                        if (data.product) {
                            console.log('Producto encontrado:', data.product);
                            fillFromProduct(data.product);
                            if (data.product?.image_url) {
                                updateImagePreview(data.product.image_url);
                            }
                            updatePortionHint('');
                            console.log('Formulario autocompletado exitosamente');
                        } else {
                            console.log('No hay datos de producto en la respuesta');
                        }
                    } else if (res.status === 404) {
                        console.log('Producto no encontrado (404), consultando OpenFoodFacts...');
                        const filled = await fillFromOpenFoodFacts(code);
                        if (!filled) {
                            console.log('Código no encontrado en OpenFoodFacts');
                            if (portionInput) {
                                portionInput.value = '';
                                updatePortionHint('');
                            }
                        }
                    } else {
                        console.log('Error en la petición, status:', res.status);
                        console.log('Consultando OpenFoodFacts directamente...');
                        const filled = await fillFromOpenFoodFacts(code);
                        if (!filled) {
                            console.log('Código no encontrado en OpenFoodFacts');
                            if (portionInput) {
                                portionInput.value = '';
                                updatePortionHint('');
                            }
                        }
                    }
                } catch (err) {
                    console.warn('Error en lookupBarcode:', err);
                    console.error('Detalles del error:', err);

                    // Intentar con OpenFoodFacts como fallback
                    console.log('Intentando OpenFoodFacts como fallback...');
                    try {
                        const filled = await fillFromOpenFoodFacts(code);
                        if (!filled) {
                            console.log('Código no encontrado en OpenFoodFacts');
                        }
                    } catch (offErr) {
                        console.error('Error también en OpenFoodFacts:', offErr);
                    }
                }
            };            barcodeInput?.addEventListener('input', (e) => {
                const code = e.target.value.trim();
                console.log('Input event detectado, código:', code);

                if (debounceId) clearTimeout(debounceId);

                if (code.length < 8) {
                    updatePortionHint('');
                    if (portionInput) portionInput.value = '';
                    return;
                }

                console.log('Código válido, programando búsqueda en 600ms...');
                debounceId = setTimeout(() => {
                    console.log('Ejecutando lookupBarcode para:', code);
                    lookupBarcode(code);
                }, 600);
            });

            // Inicializar el nuevo BarcodeScanner optimizado
            if (window.BarcodeScanner && trigger) {
                barcodeScanner = new window.BarcodeScanner(barcodeInput);

                // Configurar callback para cuando se detecte un código
                barcodeScanner.onScan = async (code) => {
                    console.log('✅ Callback onScan ejecutado con código:', code);

                    // IMPORTANTE: Asegurarse de que el código esté en el input
                    if (barcodeInput) {
                        barcodeInput.value = code;
                        console.log('✅ Código insertado en input barcode:', code);
                    }

                    // Llamar directamente a lookupBarcode sin esperar el debounce
                    await lookupBarcode(code);
                };

                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    barcodeScanner.open();
                });

                console.log('BarcodeScanner inicializado correctamente con callback');
            } else if (trigger) {
                console.warn('BarcodeScanner no está disponible');
                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    alert('El escáner de códigos de barras no está disponible. Por favor, ingresa el código manualmente.');
                });
            }

            // Botón de test para debugging
            const testBtn = document.getElementById('test-barcode');
            if (testBtn) {
                testBtn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    console.log('🧪 === INICIANDO TEST COMPLETO ===');

                    // Usar código diferente para evitar duplicados
                    const testCode = '3017620422003'; // Nutella - código de prueba
                    console.log('🧪 Paso 1: Insertando código en input:', testCode);
                    barcodeInput.value = testCode;

                    console.log('🧪 Paso 2: Llamando directamente a lookupBarcode');
                    await lookupBarcode(testCode);

                    console.log('🧪 === TEST COMPLETO FINALIZADO ===');
                    console.log('🧪 Revisa los logs arriba para ver qué pasó en cada paso');
                });
            }

            // Log antes de enviar el formulario para verificar todos los valores
            const form = document.getElementById('product-form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    console.log('📝 === FORMULARIO SIENDO ENVIADO ===');
                    console.log('Código de barras:', barcodeInput?.value);
                    console.log('Nombre:', nameInput?.value);
                    console.log('Marca:', brandInput?.value);
                    console.log('Imagen URL:', imageInput?.value);
                    console.log('Unidad base:', unitBaseInput?.value);
                    console.log('Tamaño:', unitSizeInput?.value);

                    if (!barcodeInput?.value) {
                        console.warn('⚠️ ADVERTENCIA: El código de barras está vacío!');
                    }
                });
            }
        });
    </script>
</x-layouts.app>
