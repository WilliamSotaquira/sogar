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

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Crear producto</h2>
            <form method="POST" action="{{ route('food.products.store') }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @csrf
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
                        <input name="unit_base" value="unit" class="{{ $input }}" />
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
                <div>
                    <div class="flex items-center justify-between">
                        <label class="{{ $label }}">Barcode</label>
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
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="{{ $label }}">Notas</label>
                    <textarea name="notes" class="{{ $textarea }}"></textarea>
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
        let stream = null;
        let rafId = null;
        let detector = null;

        const setStatus = (msg, tone = 'text-amber-600') => {
            if (!statusEl) return;
            statusEl.textContent = msg || '';
            statusEl.className = 'mt-1 text-[11px] ' + tone;
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
    });
</script>
