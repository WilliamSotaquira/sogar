<x-layouts.app :title="__('Productos de Alimentos')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $textarea = 'mt-1 block w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
    @endphp
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Inventario doméstico</p>
                    <h1 class="text-3xl font-bold">Productos</h1>
                    <p class="text-sm text-white/80">Define unidad base, mínimos y ubicaciones por defecto.</p>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">➕ Agregar Nuevo Producto</h2>
                <p class="text-sm text-gray-500 mt-1">Escanea el código de barras o completa los datos manualmente</p>
            </div>
            
            <form id="manual-anchor" method="POST" action="{{ route('food.products.store') }}" class="p-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @csrf
                
                {{-- Código de barras --}}
                <div class="md:col-span-2 lg:col-span-3">
                    <div class="flex items-center justify-between">
                        <label class="{{ $label }} flex items-center gap-1">
                            Código de barras
                            <span class="tooltip-trigger cursor-help text-gray-400 hover:text-gray-600" title="Escanea el código de barras del producto. Si lo encuentra en OpenFoodFacts, autocompletará todos los campos.">ℹ️</span>
                        </label>
                        <button type="button" id="scan-barcode" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Escanear con cámara</button>
                    </div>
                    <input id="barcode-input" name="barcode" class="{{ $input }}" placeholder="Escanea o escribe manualmente" />
                    <div id="barcode-scanner" class="mt-2 hidden rounded-xl border border-gray-200 bg-white p-2 text-center text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <div id="barcode-camera" class="overflow-hidden rounded-lg"></div>
                        <p class="mt-2">Apunta la cámara al código. Se cerrará automáticamente al detectar.</p>
                        <p id="barcode-status" class="mt-1 text-[11px] text-amber-600"></p>
                        <button type="button" id="close-scanner" class="mt-2 text-rose-500 hover:text-rose-600">Cerrar</button>
                    </div>
                </div>
                
                {{-- Nombre --}}
                <div class="md:col-span-1 lg:col-span-2">
                    <label class="{{ $label }} flex items-center gap-1">
                        Nombre
                        <span class="tooltip-trigger cursor-help text-gray-400 hover:text-gray-600" title="Nombre descriptivo del producto. Ej: 'Leche entera', 'Arroz blanco', 'Pan integral'">ℹ️</span>
                    </label>
                    <input name="name" required class="{{ $input }}" />
                </div>
                
                {{-- Marca --}}
                <div>
                    <label class="{{ $label }} flex items-center gap-1">
                        Marca
                        <span class="tooltip-trigger cursor-help text-gray-400 hover:text-gray-600" title="Marca comercial del producto. Ej: 'Lala', 'Verde Valle', 'Bimbo'">ℹ️</span>
                    </label>
                    <input name="brand" class="{{ $input }}" />
                </div>
                
                {{-- Tipo --}}
                <div>
                    <label class="{{ $label }} flex items-center gap-1">
                        Tipo
                        <span class="tooltip-trigger cursor-help text-gray-400 hover:text-gray-600" title="Categoría del producto (Lácteos, Granos, Frutas, etc.). Ayuda a organizar tu inventario.">ℹ️</span>
                    </label>
                    <select name="type_id" class="{{ $input }}">
                        <option value="">Selecciona</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Ubicación --}}
                <div>
                    <label class="{{ $label }} flex items-center gap-1">
                        Ubicación por defecto
                        <span class="tooltip-trigger cursor-help text-gray-400 hover:text-gray-600" title="Dónde guardas este producto normalmente. Ej: 'Refrigerador', 'Despensa', 'Congelador'">ℹ️</span>
                    </label>
                    <select name="default_location_id" class="{{ $input }}">
                        <option value="">Selecciona</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Vida útil --}}
                <div>
                    <label class="{{ $label }} flex items-center gap-1">
                        Vida útil (días)
                        <span class="tooltip-trigger cursor-help text-gray-400 hover:text-gray-600" title="Cuántos días dura el producto desde que lo compras. Leche: 7 días, Arroz: 365 días, Pan: 5 días">ℹ️</span>
                    </label>
                    <input name="shelf_life_days" type="number" min="1" max="3650" class="{{ $input }}" placeholder="7, 30, 365..." />
                </div>
                
                {{-- Tamaño del producto --}}
                <div class="md:col-span-1 lg:col-span-2">
                    <label class="{{ $label }} flex items-center gap-1">
                        Tamaño del producto
                        <span class="tooltip-trigger cursor-help text-gray-400 hover:text-gray-600" title="Cantidad y unidad del producto. Ejemplos: 500 gramos, 1 litro, 12 unidades, 750 mililitros">ℹ️</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <input name="unit_size" type="number" step="0.001" min="0.001" value="1" class="{{ $input }}" placeholder="1" required />
                        <select name="unit_base" class="{{ $input }}" required>
                            <option value="unit" selected>Unidad</option>
                            <option value="g">Gramos (g)</option>
                            <option value="kg">Kilogramos (kg)</option>
                            <option value="ml">Mililitros (ml)</option>
                            <option value="l">Litros (L)</option>
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Ej: "500 g", "1 L", "12 unidades"</p>
                </div>
                
                {{-- Stock mínimo --}}
                <div>
                    <label class="{{ $label }} flex items-center gap-1">
                        Stock mínimo
                        <span class="tooltip-trigger cursor-help text-gray-400 hover:text-gray-600" title="Cantidad mínima que debes tener. Si baja de esto, recibirás una alerta para comprar más.">ℹ️</span>
                    </label>
                    <input name="min_stock_qty" type="number" step="0.1" min="0" value="1" class="{{ $input }}" placeholder="1" />
                    <p class="text-xs text-gray-500 mt-1">Alerta cuando baje de esta cantidad</p>
                </div>
                
                {{-- Precio inicial --}}
                <div>
                    <label class="{{ $label }} flex items-center gap-1">
                        Precio inicial
                        <span class="tooltip-trigger cursor-help text-gray-400 hover:text-gray-600" title="Precio al que compraste este producto hoy. Se guardará en el histórico de precios.">ℹ️</span>
                    </label>
                    <input name="initial_price" type="number" step="0.01" min="0" class="{{ $input }}" placeholder="0.00" />
                    <p class="text-xs text-gray-500 mt-1">Opcional</p>
                </div>
                
                {{-- Vendor --}}
                <div class="md:col-span-1 lg:col-span-2">
                    <label class="{{ $label }} flex items-center gap-1">
                        Tienda/Vendor
                        <span class="tooltip-trigger cursor-help text-gray-400 hover:text-gray-600" title="Dónde compraste este producto. Útil para comparar precios entre tiendas.">ℹ️</span>
                    </label>
                    <input name="initial_vendor" type="text" class="{{ $input }}" placeholder="Ej: Walmart, Soriana" />
                    <p class="text-xs text-gray-500 mt-1">Opcional</p>
                </div>
                
                {{-- Botones --}}
                <div class="md:col-span-2 lg:col-span-3 flex items-center justify-between gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <div id="openfoodfacts-link" class="hidden">
                        <a href="#" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                            🔗 Ver en OpenFoodFacts →
                        </a>
                    </div>
                    <div class="flex-1"></div>
                    <button type="submit" class="{{ $btnPrimary }}">
                        ✓ Guardar Producto
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Listado de Productos</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $products->count() }} productos</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-100 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr class="text-left text-xs uppercase text-gray-500">
                            <th class="px-3 py-2 font-semibold">Producto</th>
                            <th class="px-3 py-2 font-semibold">Tipo</th>
                            <th class="px-3 py-2 font-semibold">Stock</th>
                            <th class="px-3 py-2 font-semibold">Mínimo</th>
                            <th class="px-3 py-2 font-semibold">Precio</th>
                            <th class="px-3 py-2 font-semibold">Rendimiento</th>
                            <th class="px-3 py-2 font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            @php
                                $stockBajo = $product->min_stock_qty && $product->current_stock < $product->min_stock_qty;
                                $performanceColor = 'gray';
                                $performanceLabel = 'Sin datos';
                                if ($product->performance_index) {
                                    if ($product->performance_index >= 80) {
                                        $performanceColor = 'emerald';
                                        $performanceLabel = 'Excelente';
                                    } elseif ($product->performance_index >= 60) {
                                        $performanceColor = 'blue';
                                        $performanceLabel = 'Bueno';
                                    } elseif ($product->performance_index >= 40) {
                                        $performanceColor = 'amber';
                                        $performanceLabel = 'Regular';
                                    } else {
                                        $performanceColor = 'rose';
                                        $performanceLabel = 'Bajo';
                                    }
                                }
                            @endphp
                            <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($product->image_url || $product->image_path)
                                            <img src="{{ $product->image_url ?? $product->image_path }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="h-12 w-12 rounded object-cover flex-shrink-0 ring-1 ring-gray-200 dark:ring-gray-700">
                                        @else
                                            <div class="h-12 w-12 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
                                                <span class="text-gray-400 text-xs">Sin img</span>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</p>
                                            @if($product->brand)
                                                <p class="text-xs text-gray-500">{{ $product->brand }}</p>
                                            @endif
                                            @if($product->barcode)
                                                <a href="https://world.openfoodfacts.org/product/{{ $product->barcode }}" 
                                                   target="_blank" 
                                                   rel="noopener"
                                                   class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                                   title="Ver en OpenFoodFacts">
                                                    🔗 {{ $product->barcode }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    @if($product->type)
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium" style="background-color: {{ $product->type->color }}1A; color: {{ $product->type->color }};">
                                            {{ $product->type->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-1">
                                        <span class="font-semibold {{ $stockBajo ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-gray-100' }}">
                                            {{ number_format($product->current_stock, 1) }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $product->unit_base }}</span>
                                        @if($stockBajo)
                                            <span class="text-rose-500" title="Stock bajo">⚠️</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $product->min_stock_qty ? number_format($product->min_stock_qty, 1) : '—' }}
                                </td>
                                <td class="px-3 py-3">
                                    @if($product->current_price)
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">${{ number_format($product->current_price, 2) }}</p>
                                            @if($product->current_vendor)
                                                <p class="text-xs text-gray-500">{{ $product->current_vendor }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">Sin precio</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @if($product->performance_index)
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-{{ $performanceColor }}-500" style="width: {{ $product->performance_index }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold text-{{ $performanceColor }}-600 dark:text-{{ $performanceColor }}-400">
                                                {{ round($product->performance_index) }}
                                            </span>
                                        </div>
                                    @else
                                        <button onclick="calculatePerformance({{ $product->id }})" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                                            Calcular
                                        </button>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('food.prices.show', $product) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 px-2 py-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20" title="Gestión de Precios">
                                            💰 Precios
                                        </a>
                                        <form method="POST" action="{{ route('food.products.destroy', $product) }}" onsubmit="return confirm('¿Eliminar {{ $product->name }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-xs font-semibold text-rose-600 hover:text-rose-700 px-2 py-1 rounded hover:bg-rose-50" title="Eliminar">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-8 text-center text-gray-500">
                                    <p class="mb-2">Aún no hay productos.</p>
                                    <p class="text-xs">Crea tu primer producto arriba ↑</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>

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
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let stream = null;
        let rafId = null;
        let detector = null;

        const setStatus = (msg, tone = 'text-amber-600') => {
            if (!statusEl) return;
            statusEl.textContent = msg || '';
            statusEl.className = 'mt-1 text-[11px] ' + tone;
        };

        const fillFromProduct = (product) => {
            if (!product) return;
            if (nameInput && !nameInput.value) nameInput.value = product.name || '';
            if (brandInput && !brandInput.value && product.brand) brandInput.value = product.brand;
            if (typeSelect && product.type_id) typeSelect.value = product.type_id;
            if (locationSelect && product.default_location_id) locationSelect.value = product.default_location_id;
            if (unitBaseInput && product.unit_base) unitBaseInput.value = product.unit_base;
            if (unitSizeInput && product.unit_size) unitSizeInput.value = product.unit_size;
            if (minStockInput && product.min_stock_qty) minStockInput.value = product.min_stock_qty;
            if (presentationInput && product.presentation_qty) presentationInput.value = product.presentation_qty;
            if (shelfLifeInput && product.shelf_life_days) shelfLifeInput.value = product.shelf_life_days;
            if (descInput && !descInput.value && product.description) descInput.value = product.description;
            if (imageInput && !imageInput.value && product.image_url) imageInput.value = product.image_url;
        };

        const fillFromOpenFoodFacts = async (code) => {
            try {
                const res = await fetch(`https://world.openfoodfacts.org/api/v0/product/${encodeURIComponent(code)}.json`);
                const data = await res.json();
                if (data?.status === 1 && data.product) {
                    const p = data.product;
                    const name = p.product_name || p.generic_name || '';
                    const brand = (p.brands_tags && p.brands_tags[0]) || p.brands || '';
                    fillFromProduct({
                        name,
                        brand,
                        unit_base: 'unit',
                        unit_size: 1,
                    });
                    setStatus('Producto sugerido desde OpenFoodFacts. Revisa y guarda.', 'text-emerald-600');
                    return true;
                }
            } catch (_) {
                // ignore
            }
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
                    setStatus('Producto encontrado y campos completados.', 'text-emerald-600');
                } else {
                    setStatus('No se encontró en tu inventario. Buscando en OpenFoodFacts...', 'text-amber-600');
                    const filled = await fillFromOpenFoodFacts(code);
                    if (!filled) {
                        setStatus('No se encontró producto. Completa los datos manualmente.', 'text-amber-600');
                    }
                }
            } catch (err) {
                console.warn(err);
                setStatus('Error al buscar el código. Intenta de nuevo.', 'text-rose-500');
            }
        };

        const stopScanner = async () => {
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
                setStatus('La cámara requiere HTTPS o localhost. Usa entrada manual o abre en https.', 'text-rose-500');
                return;
            }

            scannerWrapper?.classList.remove('hidden');
            setStatus('Buscando cámaras...');

            try {
                if (!('BarcodeDetector' in window)) {
                    setStatus('Tu navegador no soporta BarcodeDetector. Usa entrada manual.', 'text-rose-500');
                    return;
                }

                detector = new BarcodeDetector({ formats: ['ean_13', 'code_128', 'ean_8', 'qr_code'] });
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
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    try {
                        const codes = await detector.detect(imageData);
                        if (codes.length) {
                            const text = codes[0].rawValue || codes[0].cornerPoints || '';
                            if (text) {
                                barcodeInput.value = text;
                                setStatus('Código detectado: ' + text, 'text-emerald-600');
                                stopScanner();
                                lookupBarcode(text);
                                return;
                            }
                        }
                    } catch (err) {
                        // ignore decode errors
                    }
                    rafId = requestAnimationFrame(scan);
                };
                rafId = requestAnimationFrame(scan);
            } catch (err) {
                console.warn('Scanner error', err);
                setStatus('No se pudo acceder a la cámara. Revisa permisos o usa entrada manual.', 'text-rose-500');
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

        // Autocompletar cuando se escribe código de barras
        let barcodeTimeout;
        barcodeInput?.addEventListener('input', async (e) => {
            clearTimeout(barcodeTimeout);
            const code = e.target.value.trim();

            // Solo buscar si tiene al menos 8 caracteres
            if (code.length < 8) {
                setStatus('');
                
                // Rehabilitar botón si se borró el barcode
                const submitBtn = document.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitBtn.title = '';
                }
                
                // Remover advertencia
                const warningDiv = document.getElementById('duplicate-warning');
                if (warningDiv) {
                    warningDiv.remove();
                }
                
                return;
            }

            setStatus('🔍 Buscando producto...', 'text-blue-600');

            barcodeTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`/food/barcode/${encodeURIComponent(code)}`, {
                        headers: {
                            'X-CSRF-TOKEN': csrf || '',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (res.ok) {
                        const data = await res.json();
                        
                        if (data.found) {
                            const productData = data.data;

                            // Autocompletar todos los campos
                            if (productData.name && !nameInput.value) {
                                nameInput.value = productData.name;
                            }

                            if (productData.brand) {
                                brandInput.value = productData.brand;
                            }

                            if (productData.type_id) {
                                typeSelect.value = productData.type_id;
                            }

                            if (productData.location_id) {
                                locationSelect.value = productData.location_id;
                            }

                            if (productData.unit_base) {
                                unitBaseInput.value = productData.unit_base;
                            }

                            if (productData.unit_size) {
                                unitSizeInput.value = productData.unit_size;
                            }

                            if (productData.min_stock_qty) {
                                minStockInput.value = productData.min_stock_qty;
                            }

                            if (productData.shelf_life_days || productData.suggested_shelf_life) {
                                shelfLifeInput.value = productData.shelf_life_days || productData.suggested_shelf_life;
                            }

                            // Guardar imagen en campo hidden para backend
                            if (productData.image_url) {
                                let hiddenImageInput = document.querySelector('input[name="image_url"]');
                                if (!hiddenImageInput) {
                                    hiddenImageInput = document.createElement('input');
                                    hiddenImageInput.type = 'hidden';
                                    hiddenImageInput.name = 'image_url';
                                    document.querySelector('form').appendChild(hiddenImageInput);
                                }
                                hiddenImageInput.value = productData.image_url;
                            }

                            // Mostrar enlace a OpenFoodFacts
                            const offLink = document.getElementById('openfoodfacts-link');
                            if (offLink && code) {
                                const link = offLink.querySelector('a');
                                link.href = `https://world.openfoodfacts.org/product/${code}`;
                                offLink.classList.remove('hidden');
                            }

                            // Mensaje de éxito
                            if (data.source === 'local') {
                                setStatus('⚠️ Este producto ya existe en tu catálogo (ID: ' + productData.id + ')', 'text-rose-600 font-bold');
                                
                                // Deshabilitar botón guardar
                                const submitBtn = document.querySelector('button[type="submit"]');
                                if (submitBtn) {
                                    submitBtn.disabled = true;
                                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                                    submitBtn.title = 'Este producto ya existe. Borra el código de barras para crear uno nuevo.';
                                }
                                
                                // Agregar mensaje visual
                                const barcodeContainer = barcodeInput.parentElement;
                                let warningDiv = document.getElementById('duplicate-warning');
                                if (!warningDiv) {
                                    warningDiv = document.createElement('div');
                                    warningDiv.id = 'duplicate-warning';
                                    warningDiv.className = 'mt-2 p-3 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg';
                                    warningDiv.innerHTML = `
                                        <p class="text-sm text-rose-800 dark:text-rose-200 font-semibold">
                                            ⚠️ Este producto ya existe en tu catálogo
                                        </p>
                                        <p class="text-xs text-rose-600 dark:text-rose-300 mt-1">
                                            <strong>Opción 1:</strong> Borra el código de barras para crear un producto nuevo con datos similares<br>
                                            <strong>Opción 2:</strong> Ve al listado abajo y edítalo directamente
                                        </p>
                                    `;
                                    barcodeContainer.appendChild(warningDiv);
                                }
                            } else {
                                setStatus('✅ Datos cargados desde OpenFoodFacts. Revisa y guarda.', 'text-emerald-600');
                                
                                // Habilitar botón guardar
                                const submitBtn = document.querySelector('button[type="submit"]');
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                    submitBtn.title = '';
                                }
                                
                                // Remover mensaje de advertencia si existe
                                const warningDiv = document.getElementById('duplicate-warning');
                                if (warningDiv) {
                                    warningDiv.remove();
                                }
                            }
                        } else {
                            setStatus('⚠️ Código no encontrado. Completa los datos manualmente.', 'text-amber-600');
                        }
                    } else {
                        setStatus('❌ Error al buscar. Intenta nuevamente.', 'text-rose-500');
                    }
                } catch (err) {
                    console.error(err);
                    setStatus('❌ Error de conexión. Verifica tu internet.', 'text-rose-500');
                }
            }, 800);
        });

        // Función para calcular rendimiento de producto
        window.calculatePerformance = async function(productId) {
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Calculando...';
            button.disabled = true;

            try {
                const res = await fetch(`/api/food/products/${productId}/performance`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf || '',
                        'Accept': 'application/json',
                    },
                });

                if (res.ok) {
                    location.reload();
                } else {
                    alert('Error al calcular rendimiento');
                    button.textContent = originalText;
                    button.disabled = false;
                }
            } catch (err) {
                console.error(err);
                alert('Error al calcular rendimiento');
                button.textContent = originalText;
                button.disabled = false;
            }
        };
    });
</script>

