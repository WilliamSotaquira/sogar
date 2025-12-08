<x-layouts.app :title="__('Nuevo Producto de Alimentos')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $textarea = 'mt-1 block w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
    @endphp

    <div class="mx-auto w-full max-w-4xl space-y-6">
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-4 md:flex-row md:items-center md:justify-between text-white">
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
                        <section class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-4 md:p-5 space-y-4">
                            <header class="flex flex-col gap-1">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Identificación</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Estos datos nos ayudan a localizar y sugerir el producto.</p>
                            </header>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <label class="{{ $label }}">Código de barras</label>
                                        <button type="button" id="scan-barcode" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Escanear con cámara</button>
                                    </div>
                                    <input id="barcode-input" name="barcode" value="{{ old('barcode') }}" class="{{ $input }}" placeholder="Escanéalo o escríbelo" autocomplete="off" aria-describedby="barcode-helper" />
                                    <p id="barcode-helper" class="sr-only">Un código de barras válido permite autocompletar la información.</p>
                                    <div id="barcode-scanner" class="mt-2 hidden rounded-xl border border-gray-200 bg-white p-2 text-center text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        <div id="barcode-camera" class="overflow-hidden rounded-lg"></div>
                                        <p class="mt-2">Apunta la cámara al código. Se cerrará automáticamente al detectar.</p>
                                        <p id="barcode-status" class="mt-1 text-[11px] text-amber-600"></p>
                                        <button type="button" id="close-scanner" class="mt-2 text-rose-500 hover:text-rose-600">Cerrar</button>
                                    </div>
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

                        <section class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-4 md:p-5 space-y-4">
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

                        <section class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-4 md:p-5 space-y-4">
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
                        <div class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-5 space-y-4">
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
                        <div class="rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 p-4 text-sm text-gray-600 dark:text-gray-400 space-y-2">
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
            const scannerWrapper = document.getElementById('barcode-scanner');
            const cameraEl = document.getElementById('barcode-camera');
            const barcodeInput = document.getElementById('barcode-input');
            const closeBtn = document.getElementById('close-scanner');
            const statusEl = document.getElementById('barcode-status');
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
            let stream = null;
            let rafId = null;
            let detector = null;
            let debounceId = null;

            const setStatus = (msg, tone = 'text-amber-600') => {
                if (!statusEl) return;
                statusEl.textContent = msg || '';
                statusEl.className = 'mt-1 text-[11px] ' + tone;
            };

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
                if (!product) return;
                if (product.name && nameInput && !nameInput.value) nameInput.value = product.name;
                if (product.brand && brandInput && !brandInput.value) brandInput.value = product.brand;
                if (product.type_id && typeSelect) typeSelect.value = product.type_id;
                if (product.default_location_id && locationSelect) locationSelect.value = product.default_location_id;
                if (product.unit_base && unitBaseInput) unitBaseInput.value = product.unit_base;
                if (product.unit_size && unitSizeInput) unitSizeInput.value = product.unit_size;
                if (product.min_stock_qty && minStockInput) minStockInput.value = product.min_stock_qty;
                if (product.shelf_life_days && shelfLifeInput) shelfLifeInput.value = product.shelf_life_days;
                if (product.description && descriptionInput && !descriptionInput.value) descriptionInput.value = product.description;
                if (imageInput && product.image_url) {
                    imageInput.value = product.image_url;
                    updateImagePreview(product.image_url);
                }
                if (portionInput && product.presentation_qty) {
                    portionInput.value = product.presentation_qty;
                    updatePortionHint(product.portion_text || '', product.presentation_qty, product.unit_base);
                } else if (portionInput && product.portion_qty) {
                    portionInput.value = product.portion_qty;
                    updatePortionHint(product.portion_text || '', product.portion_qty, product.portion_unit || product.unit_base);
                }
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
                try {
                    const res = await fetch(`https://world.openfoodfacts.org/api/v0/product/${encodeURIComponent(code)}.json`);
                    const data = await res.json();
                    if (data?.status === 1 && data.product) {
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
                        if (imageUrl) {
                            updateImagePreview(imageUrl);
                            if (imageInput) {
                                imageInput.value = imageUrl;
                            }
                        }

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

                        setStatus('Producto sugerido desde OpenFoodFacts. Revisa y guarda.', 'text-emerald-600');
                        return true;
                    }
                } catch (_) {
                    // ignore
                }
                updatePortionHint('');
                return false;
            };

            const lookupBarcode = async (code) => {
                if (!code) return;
                setStatus('Buscando producto por código...');
                try {
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
                        if (res.ok) {
                            const data = await res.json();
                            fillFromProduct(data.product);
                            if (data.product?.image_url) {
                                updateImagePreview(data.product.image_url);
                            }
                            updatePortionHint('');
                        setStatus('Producto encontrado en tu inventario.', 'text-emerald-600');
                    } else {
                        setStatus('No se encontró en tu inventario. Consultando OpenFoodFacts...', 'text-amber-600');
                        const filled = await fillFromOpenFoodFacts(code);
                        if (!filled) {
                            setStatus('Código no encontrado. Completa los datos manualmente.', 'text-amber-600');
                            if (portionInput) {
                                portionInput.value = '';
                                updatePortionHint('');
                            }
                        }
                    }
                } catch (err) {
                    console.warn(err);
                    setStatus('Error al buscar el código. Intenta de nuevo.', 'text-rose-500');
                }
            };

            barcodeInput?.addEventListener('input', (e) => {
                if (debounceId) clearTimeout(debounceId);
                const code = e.target.value.trim();
                if (code.length < 8) {
                    setStatus('');
                    updatePortionHint('');
                    if (portionInput) portionInput.value = '';
                    return;
                }
                setStatus('🔍 Buscando producto...', 'text-blue-600');
                debounceId = setTimeout(() => lookupBarcode(code), 600);
            });

            const stopScanner = () => {
                setStatus('');
                if (rafId) cancelAnimationFrame(rafId);
                rafId = null;
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                    stream = null;
                }
                detector = null;
                cameraEl.innerHTML = '';
                scannerWrapper?.classList.add('hidden');
            };

            const startScanner = async () => {
                if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                    setStatus('La cámara requiere HTTPS o localhost.', 'text-rose-500');
                    return;
                }

                scannerWrapper?.classList.remove('hidden');
                setStatus('Buscando cámaras...');

                try {
                    if (typeof window.ensureBarcodeDetector === 'function') {
                        try {
                            await window.ensureBarcodeDetector();
                        } catch (polyfillErr) {
                            console.warn(polyfillErr);
                        }
                    }

                    if (!('BarcodeDetector' in window)) {
                        setStatus('Tu navegador no soporta BarcodeDetector. Usa entrada manual.', 'text-rose-500');
                        return;
                    }

                    detector = new BarcodeDetector({ formats: ['ean_13', 'code_128', 'ean_8'] });
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { ideal: 'environment' } },
                        audio: false,
                    });

                    const video = document.createElement('video');
                    video.setAttribute('playsinline', true);
                    video.muted = true;
                    video.srcObject = stream;
                    await video.play();
                    cameraEl.innerHTML = '';
                    cameraEl.appendChild(video);
                    setStatus('Escaneando... apunta al código.');

                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    const scan = async () => {
                        if (!video.videoWidth) {
                            rafId = requestAnimationFrame(scan);
                            return;
                        }
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        try {
                            const codes = await detector.detect(canvas);
                            if (codes.length) {
                                const text = codes[0].rawValue;
                                if (text) {
                                    barcodeInput.value = text;
                                    setStatus('Código detectado: ' + text, 'text-emerald-600');
                                    stopScanner();
                                    lookupBarcode(text);
                                    return;
                                }
                            }
                        } catch (_) {
                            // ignore
                        }
                        rafId = requestAnimationFrame(scan);
                    };
                    rafId = requestAnimationFrame(scan);
                } catch (err) {
                    console.warn(err);
                    setStatus('No se pudo acceder a la cámara. Revisa permisos.', 'text-rose-500');
                }
            };

            trigger?.addEventListener('click', (e) => {
                e.preventDefault();
                startScanner();
            });

            closeBtn?.addEventListener('click', (e) => {
                e.preventDefault();
                stopScanner();
            });
        });
    </script>
</x-layouts.app>
