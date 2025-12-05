<x-layouts.app :title="__('Inventario de Alimentos')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
    @endphp
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Inventario</p>
                    <h1 class="text-3xl font-bold">Stock por ubicación</h1>
                    <p class="text-sm text-white/80">Filtra por alacena o tipo y revisa caducidades.</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50 mb-2">Buscar producto en stock</h3>
            <div class="flex flex-wrap gap-3 items-center">
                <div class="flex items-center gap-2">
                    <input id="inventory-barcode-input" class="{{ $input }}" placeholder="Escanea o escribe código de barras" />
                    <button type="button" id="inventory-scan" class="{{ $btnSecondary }}">Escanear</button>
                </div>
                <button type="button" id="inventory-search" class="{{ $btnSecondary }}">Buscar en stock</button>
                <span id="inventory-status" class="text-xs text-gray-500 dark:text-gray-400"></span>
            </div>
            <div id="inventory-scanner" class="mt-2 hidden rounded-xl border border-gray-200 bg-white p-2 text-center text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <div id="inventory-camera" class="overflow-hidden rounded-lg"></div>
                <p class="mt-2">Apunta la cámara al código. Se cierra al detectar.</p>
                <button type="button" id="inventory-close" class="mt-2 text-rose-500 hover:text-rose-600">Cerrar</button>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <form class="flex flex-wrap gap-2 items-center">
                <select name="location_id" class="{{ $input }}">
                    <option value="">Todas las ubicaciones</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected(request('location_id') == $loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
                <select name="type_id" class="{{ $input }}">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" @selected(request('type_id') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
                <button class="{{ $btnSecondary }}" type="submit">Filtrar</button>
            </form>
            <a href="{{ route('food.products.index') }}" class="{{ $btnSecondary }}">Productos</a>
            <a href="{{ route('food.purchases.index') }}" class="{{ $btnSecondary }}">Compras</a>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Lotes</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-100 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr class="text-left text-xs uppercase text-gray-500">
                            <th class="px-3 py-2 font-semibold">Producto</th>
                            <th class="px-3 py-2 font-semibold">Ubicación</th>
                            <th class="px-3 py-2 font-semibold">Tipo</th>
                            <th class="px-3 py-2 font-semibold">Qty</th>
                            <th class="px-3 py-2 font-semibold">Caduca</th>
                            <th class="px-3 py-2 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                            @php
                                $expires = $batch->expires_on ? \Carbon\Carbon::parse($batch->expires_on) : null;
                                $days = $expires ? now()->diffInDays($expires, false) : null;
                            @endphp
                            <tr class="border-t border-gray-100 dark:border-gray-800 inventory-row" data-product-id="{{ $batch->product_id }}">
                                <td class="px-3 py-2 font-medium">{{ $batch->product->name }}</td>
                                <td class="px-3 py-2">{{ $batch->location?->name ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $batch->product->type?->name ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $batch->qty_remaining_base }} {{ $batch->unit_base }}</td>
                                <td class="px-3 py-2">
                                    @if($expires)
                                        {{ $expires->format('d M') }}
                                        <span class="text-xs text-gray-500">({{ $days }} días)</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <span class="hero-chip text-xs">
                                        @if($batch->status === 'ok')
                                            OK
                                        @else
                                            {{ $batch->status }}
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-gray-500">Aún no hay stock.</td>
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
        const input = document.getElementById('inventory-barcode-input');
        const scanBtn = document.getElementById('inventory-scan');
        const searchBtn = document.getElementById('inventory-search');
        const statusEl = document.getElementById('inventory-status');
        const scannerWrapper = document.getElementById('inventory-scanner');
        const cameraEl = document.getElementById('inventory-camera');
        const closeBtn = document.getElementById('inventory-close');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let stream = null;
        let rafId = null;
        let detector = null;

        const setStatus = (msg, tone = 'text-amber-600') => {
            if (!statusEl) return;
            statusEl.textContent = msg || '';
            statusEl.className = 'text-xs ' + tone;
        };

        const stopScanner = async () => {
            if (rafId) cancelAnimationFrame(rafId);
            rafId = null;
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
            }
            detector = null;
            cameraEl.innerHTML = '';
            scannerWrapper?.classList.add('hidden');
            setStatus('');
        };

        const highlightProduct = (productId) => {
            document.querySelectorAll('.inventory-row').forEach(row => row.classList.remove('bg-emerald-50', 'dark:bg-emerald-900/20'));
            const row = document.querySelector(`.inventory-row[data-product-id="${productId}"]`);
            if (row) {
                row.classList.add('bg-emerald-50', 'dark:bg-emerald-900/20');
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                setStatus('No hay stock para este código en el listado.', 'text-amber-600');
            }
        };

        const lookupAndHighlight = async (code) => {
            if (!code) return;
            setStatus('Buscando producto...');
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
                    highlightProduct(data.product.id);
                    setStatus('Producto encontrado en tu inventario.', 'text-emerald-600');
                } else {
                    setStatus('No se encontró el producto. ¿Lo quieres crear primero?', 'text-amber-600');
                }
            } catch (err) {
                console.warn(err);
                setStatus('Error al buscar el código.', 'text-rose-500');
            }
        };

        const startScanner = async () => {
            if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                setStatus('La cámara requiere HTTPS o localhost.', 'text-rose-500');
                return;
            }
            scannerWrapper?.classList.remove('hidden');
            setStatus('Buscando cámaras...');
            try {
                if (!('BarcodeDetector' in window)) {
                    setStatus('Tu navegador no soporta BarcodeDetector.', 'text-rose-500');
                    return;
                }
                detector = new BarcodeDetector({ formats: ['ean_13', 'code_128', 'ean_8', 'qr_code'] });
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
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
                            const text = codes[0].rawValue || '';
                            if (text) {
                                input.value = text;
                                setStatus('Código detectado: ' + text, 'text-emerald-600');
                                await stopScanner();
                                lookupAndHighlight(text);
                                return;
                            }
                        }
                    } catch (_) {}
                    rafId = requestAnimationFrame(scan);
                };
                rafId = requestAnimationFrame(scan);
            } catch (err) {
                console.warn(err);
                setStatus('No se pudo acceder a la cámara.', 'text-rose-500');
            }
        };

        scanBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            startScanner();
        });

        closeBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            stopScanner();
        });

        searchBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            lookupAndHighlight(input?.value);
        });

        input?.addEventListener('change', (e) => lookupAndHighlight(e.target.value));
    });
</script>
