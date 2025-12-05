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

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <div class="flex flex-col gap-3 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/60">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">¿Cómo quieres agregar?</h2>
                <div class="grid gap-3 md:grid-cols-2">
                    <button
                        type="button"
                        class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-left text-gray-800 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:text-gray-50"
                        onclick="document.getElementById('barcode-input').focus(); document.getElementById('scan-barcode').scrollIntoView({behavior:'smooth', block:'center'});"
                    >
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-100">
                            📷
                        </span>
                        <div>
                            <p class="text-sm font-semibold">Usar escáner</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Escanea el código y autocompleta con OpenFoodFacts.</p>
                        </div>
                    </button>
                    <button
                        type="button"
                        class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-left text-gray-800 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:text-gray-50"
                        onclick="document.getElementById('manual-anchor').scrollIntoView({behavior:'smooth', block:'start'});"
                    >
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-100">
                            ✍️
                        </span>
                        <div>
                            <p class="text-sm font-semibold">Ingreso manual</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Completa nombre, marca, unidades y ubicación.</p>
                        </div>
                    </button>
                </div>
            </div>

            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Crear producto</h2>
            <form id="manual-anchor" method="POST" action="{{ route('food.products.store') }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @csrf
                <div class="md:col-span-2 lg:col-span-3">
                    <div class="flex items-center justify-between">
                        <label class="{{ $label }}">Código de barras</label>
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
                <div>
                    <label class="{{ $label }}">Nombre</label>
                    <input name="name" required class="{{ $input }}" />
                </div>
                <div>
                    <label class="{{ $label }}">Marca</label>
                    <input name="brand" class="{{ $input }}" />
                </div>
                <div>
                    <label class="{{ $label }}">Tipo</label>
                    <select name="type_id" class="{{ $input }}">
                        <option value="">Selecciona</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">Ubicación por defecto</label>
                    <select name="default_location_id" class="{{ $input }}">
                        <option value="">Selecciona</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="{{ $label }}">Unidad base</label>
                        <select name="unit_base" class="{{ $input }}">
                            <option value="g">Gramos</option>
                            <option value="ml">Mililitros</option>
                            <option value="unit" selected>Unidad</option>
                            <option value="docena">Docena</option>
                            <option value="manojo">Manojo</option>
                            <option value="sixpack">Sixpack</option>
                            <option value="bolsa">Bolsa</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">Factor unidad</label>
                        <input name="unit_size" value="1" class="{{ $input }}" />
                    </div>
                    <div>
                        <label class="{{ $label }}">Mínimo</label>
                        <input name="min_stock_qty" value="0" class="{{ $input }}" />
                    </div>
                </div>
                <div>
                    <label class="{{ $label }}">Vida útil (días)</label>
                    <input name="shelf_life_days" class="{{ $input }}" />
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="{{ $label }}">Notas</label>
                    <textarea name="notes" class="{{ $textarea }}"></textarea>
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="{{ $label }}">Descripción (auto desde OFF si existe)</label>
                    <textarea name="description" class="{{ $textarea }}" placeholder="Se autocompletará si OpenFoodFacts trae texto"></textarea>
                </div>
                <div>
                    <label class="{{ $label }}">Imagen (URL)</label>
                    <input name="image_url" class="{{ $input }}" placeholder="Se autocompletará si OpenFoodFacts trae imagen" />
                </div>
                <div class="md:col-span-2 lg:col-span-3 flex justify-end">
                    <button type="submit" class="{{ $btnPrimary }}">Guardar</button>
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Listado</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-100 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr class="text-left text-xs uppercase text-gray-500">
                            <th class="px-3 py-2 font-semibold">Nombre</th>
                            <th class="px-3 py-2 font-semibold">Imagen</th>
                            <th class="px-3 py-2 font-semibold">Tipo</th>
                            <th class="px-3 py-2 font-semibold">Ubicación</th>
                            <th class="px-3 py-2 font-semibold">Unidad base</th>
                            <th class="px-3 py-2 font-semibold">Mínimo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="px-3 py-2 font-medium">{{ $product->name }}</td>
                                <td class="px-3 py-2">
                                    @if($product->image_path || $product->image_url)
                                        <img src="{{ $product->image_path ?? $product->image_url }}" alt="{{ $product->name }}" class="h-10 w-10 rounded object-cover">
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-2">{{ $product->type?->name ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $product->defaultLocation?->name ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $product->unit_base }} ({{ $product->unit_size }})</td>
                                <td class="px-3 py-2">{{ $product->min_stock_qty ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-gray-500">Aún no hay productos.</td>
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
        const unitBaseInput = document.querySelector('input[name="unit_base"]');
        const unitSizeInput = document.querySelector('input[name="unit_size"]');
        const minStockInput = document.querySelector('input[name="min_stock_qty"]');
        const shelfLifeInput = document.querySelector('input[name="shelf_life_days"]');
        const descInput = document.querySelector('textarea[name="description"]');
        const imageInput = document.querySelector('input[name="image_url"]');
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

        barcodeInput?.addEventListener('change', (e) => lookupBarcode(e.target.value));
    });
</script>
