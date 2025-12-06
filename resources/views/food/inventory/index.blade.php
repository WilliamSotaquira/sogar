<x-layouts.app :title="__('Inventario de Alimentos')">
    @php
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
        $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
        
        // Calcular estadísticas
        $totalItems = $batches->count();
        $totalProducts = $batches->pluck('product_id')->unique()->count();
        $expiringCount = $batches->filter(function($batch) {
            if (!$batch->expires_on) return false;
            $days = now()->diffInDays(\Carbon\Carbon::parse($batch->expires_on), false);
            return $days >= 0 && $days <= 7;
        })->count();
        $expiredCount = $batches->filter(function($batch) {
            if (!$batch->expires_on) return false;
            return now()->gt(\Carbon\Carbon::parse($batch->expires_on));
        })->count();
    @endphp
    <div class="mx-auto w-full max-w-7xl space-y-6">
        {{-- Header --}}
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Inventario doméstico</p>
                    <h1 class="text-3xl font-bold">Stock por Ubicación</h1>
                    <p class="text-sm text-white/80">Revisa lotes, ubicaciones y fechas de caducidad</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('food.products.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm transition">
                        📦 Productos
                    </a>
                    <a href="{{ route('food.purchases.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm transition">
                        🛒 Compras
                    </a>
                </div>
            </div>
        </div>

        {{-- Métricas de resumen --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <span class="text-2xl">📦</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalItems }}</p>
                        <p class="text-xs text-gray-500">Lotes totales</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <span class="text-2xl">🏷️</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalProducts }}</p>
                        <p class="text-xs text-gray-500">Productos únicos</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <span class="text-2xl">⚠️</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $expiringCount }}</p>
                        <p class="text-xs text-gray-500">Por vencer (7 días)</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                        <span class="text-2xl">🚫</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $expiredCount }}</p>
                        <p class="text-xs text-gray-500">Caducados</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Búsqueda por código de barras --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50">🔍 Buscar producto en stock</h3>
                <span id="inventory-status" class="text-xs text-gray-500 dark:text-gray-400"></span>
            </div>
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[250px]">
                    <label class="{{ $label }}">Código de barras</label>
                    <input id="inventory-barcode-input" class="{{ $input }}" placeholder="Escanea o escribe el código" />
                </div>
                <button type="button" id="inventory-scan" class="{{ $btnSecondary }}">
                    📷 Escanear
                </button>
                <button type="button" id="inventory-search" class="{{ $btnPrimary }}">
                    🔎 Buscar
                </button>
            </div>
            <div id="inventory-scanner" class="mt-3 hidden rounded-xl border border-gray-200 bg-white p-3 text-center text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <div id="inventory-camera" class="overflow-hidden rounded-lg"></div>
                <p class="mt-2">Apunta la cámara al código de barras. Se cerrará automáticamente al detectarlo.</p>
                <button type="button" id="inventory-close" class="mt-2 text-rose-500 hover:text-rose-600 font-semibold">✕ Cerrar</button>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50 mb-3">⚙️ Filtros</h3>
            <form class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="{{ $label }}">Ubicación</label>
                    <select name="location_id" class="{{ $input }}" onchange="this.form.submit()">
                        <option value="">Todas las ubicaciones</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" @selected(request('location_id') == $loc->id)>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="{{ $label }}">Tipo de producto</label>
                    <select name="type_id" class="{{ $input }}" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected(request('type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request('location_id') || request('type_id'))
                    <a href="{{ route('food.inventory.index') }}" class="{{ $btnSecondary }}">
                        ✕ Limpiar filtros
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabla de lotes --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Listado de Lotes</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $batches->count() }} lotes</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-100 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr class="text-left text-xs uppercase text-gray-500">
                            <th class="px-3 py-2 font-semibold">Producto</th>
                            <th class="px-3 py-2 font-semibold">Ubicación</th>
                            <th class="px-3 py-2 font-semibold">Cantidad</th>
                            <th class="px-3 py-2 font-semibold">Precio</th>
                            <th class="px-3 py-2 font-semibold">Caduca</th>
                            <th class="px-3 py-2 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                            @php
                                $expires = $batch->expires_on ? \Carbon\Carbon::parse($batch->expires_on) : null;
                                $days = $expires ? now()->diffInDays($expires, false) : null;
                                
                                // Determinar color de alerta de caducidad
                                $expiryClass = 'text-gray-600 dark:text-gray-300';
                                $expiryBadge = null;
                                if ($days !== null) {
                                    if ($days < 0) {
                                        $expiryClass = 'text-rose-600 dark:text-rose-400 font-semibold';
                                        $expiryBadge = 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300';
                                    } elseif ($days <= 3) {
                                        $expiryClass = 'text-rose-600 dark:text-rose-400 font-semibold';
                                        $expiryBadge = 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300';
                                    } elseif ($days <= 7) {
                                        $expiryClass = 'text-amber-600 dark:text-amber-400 font-semibold';
                                        $expiryBadge = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
                                    }
                                }

                                // Obtener precio actual
                                $latestPrice = \App\Models\FoodPrice::where('product_id', $batch->product_id)
                                    ->orderBy('captured_on', 'desc')
                                    ->orderBy('created_at', 'desc')
                                    ->first();
                                $currentPrice = $latestPrice ? $latestPrice->price_per_base : null;
                                $currentVendor = $latestPrice ? $latestPrice->vendor : null;
                            @endphp
                            <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition inventory-row" data-product-id="{{ $batch->product_id }}">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($batch->product->image_url || $batch->product->image_path)
                                            <img src="{{ $batch->product->image_url ?? $batch->product->image_path }}"
                                                 alt="{{ $batch->product->name }}"
                                                 class="h-12 w-12 rounded object-cover flex-shrink-0 ring-1 ring-gray-200 dark:ring-gray-700">
                                        @else
                                            <div class="h-12 w-12 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
                                                <span class="text-gray-400 text-xs">Sin img</span>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $batch->product->name }}</p>
                                            @if($batch->product->brand)
                                                <p class="text-xs text-gray-500">{{ $batch->product->brand }}</p>
                                            @endif
                                            @if($batch->product->type)
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium mt-1" style="background-color: {{ $batch->product->type->color }}1A; color: {{ $batch->product->type->color }};">
                                                    {{ $batch->product->type->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    @if($batch->location)
                                        <div class="flex items-center gap-2">
                                            <span class="h-3 w-3 rounded-full" style="background-color: {{ $batch->location->color }};"></span>
                                            <span class="text-gray-900 dark:text-gray-100">{{ $batch->location->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($batch->qty_remaining_base, 1) }} {{ $batch->unit_base }}</p>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">${{ $currentPrice ? number_format($currentPrice, 0, ',', '.') : '0' }}</p>
                                        @if($currentPrice && $currentVendor)
                                            <p class="text-xs text-gray-500">{{ $currentVendor }}</p>
                                        @elseif(!$currentPrice)
                                            <a href="{{ route('food.prices.show', $batch->product) }}" class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 hover:underline">
                                                + Agregar
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    @if($expires)
                                        <div>
                                            <p class="{{ $expiryClass }}">{{ $expires->format('d M Y') }}</p>
                                            @if($days !== null)
                                                @if($days < 0)
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $expiryBadge }} mt-1">
                                                        🚫 Caducado hace {{ abs($days) }} días
                                                    </span>
                                                @elseif($days === 0)
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $expiryBadge }} mt-1">
                                                        ⚠️ Caduca hoy
                                                    </span>
                                                @elseif($days <= 7)
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $expiryBadge }} mt-1">
                                                        ⚠️ {{ $days }} {{ $days === 1 ? 'día' : 'días' }}
                                                    </span>
                                                @else
                                                    <p class="text-xs text-gray-500">{{ $days }} días</p>
                                                @endif
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @if($batch->status === 'ok')
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                            ✓ OK
                                        </span>
                                    @elseif($batch->status === 'expired')
                                        <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                                            🚫 Caducado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            {{ ucfirst($batch->status) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-12 text-center">
                                    <div class="inline-flex h-16 w-16 rounded-full bg-gray-100 dark:bg-gray-800 items-center justify-center mb-3">
                                        <span class="text-3xl">📦</span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay lotes en inventario</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Agrega tu primera compra para ver el stock aquí</p>
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
            document.querySelectorAll('.inventory-row').forEach(row => row.classList.remove('bg-emerald-50', 'dark:bg-emerald-900/20', 'ring-2', 'ring-emerald-500'));
            const rows = document.querySelectorAll(`.inventory-row[data-product-id="${productId}"]`);
            if (rows.length > 0) {
                rows.forEach(row => {
                    row.classList.add('bg-emerald-50', 'dark:bg-emerald-900/20', 'ring-2', 'ring-emerald-500');
                });
                rows[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                setStatus(`Encontrado: ${rows.length} ${rows.length === 1 ? 'lote' : 'lotes'}`, 'text-emerald-600');
            } else {
                setStatus('No hay stock para este código en el listado actual.', 'text-amber-600');
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
                    setStatus('Tu navegador no soporta BarcodeDetector. Usa entrada manual.', 'text-rose-500');
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
                setStatus('No se pudo acceder a la cámara. Revisa permisos.', 'text-rose-500');
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

        input?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                lookupAndHighlight(e.target.value);
            }
        });
    });
</script>
