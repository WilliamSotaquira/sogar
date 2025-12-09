@props(['locations' => [], 'types' => []])

<div id="quick-product-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm" onclick="if(event.target===this) closeQuickProductModal()">
    <div class="mx-4 w-full max-w-lg rounded-2xl bg-white shadow-2xl dark:bg-gray-900" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Producto Rápido</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Agrega un producto con lo esencial</p>
            </div>
            <button type="button" onclick="closeQuickProductModal()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-800">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form id="quick-product-form" class="p-6 space-y-4">
            @csrf

            <!-- Barcode Scanner -->
            <div class="flex items-center gap-3 rounded-lg bg-emerald-50 p-3 dark:bg-emerald-900/20">
                <button type="button" id="quick-scan-barcode" class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>
                <div class="flex-1">
                    <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Código de barras</label>
                    <input id="quick-barcode" name="barcode" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-gray-300 focus:ring-2 focus:ring-emerald-500 dark:bg-gray-800 dark:ring-gray-600" placeholder="Escanea o escribe" />
                </div>
            </div>

            <!-- Essential Fields -->
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del producto *</label>
                    <input id="quick-name" name="name" required class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" placeholder="Ej: Leche entera 1L" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Marca</label>
                        <input id="quick-brand" name="brand" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" placeholder="Ej: Lala" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                        <select id="quick-type" name="type_id" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">Selecciona</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Add to Inventory Option -->
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" id="quick-add-inventory" name="add_to_inventory" class="h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-2 focus:ring-emerald-500 dark:border-gray-600" />
                    <div class="flex-1">
                        <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Agregar a inventario ahora</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Registra la cantidad y ubicación</span>
                    </div>
                </label>

                <div id="quick-inventory-fields" class="mt-3 hidden space-y-3 border-t border-gray-200 pt-3 dark:border-gray-700">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Cantidad</label>
                            <input type="number" name="inventory_qty" step="0.1" min="0.1" value="1" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Unidad</label>
                            <select name="unit_base" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                <option value="unit">Unidad</option>
                                <option value="g">Gramos</option>
                                <option value="kg">Kilogramos</option>
                                <option value="ml">Mililitros</option>
                                <option value="l">Litros</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Ubicación</label>
                        <select name="location_id" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">Selecciona ubicación</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Fecha de vencimiento (opcional)</label>
                        <input type="date" name="expiry_date" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                <button type="button" onclick="closeQuickProductModal()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                    Cancelar
                </button>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <span id="quick-submit-text">Guardar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Camera Modal for Barcode Scanning -->
<div id="camera-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/90" onclick="if(event.target===this) stopBarcodeScanner()">
    <div class="relative w-full max-w-2xl" onclick="event.stopPropagation()">
        <button onclick="stopBarcodeScanner()" class="absolute right-4 top-4 z-10 rounded-lg bg-white/10 p-2 text-white backdrop-blur-sm hover:bg-white/20">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div id="barcode-scanner" class="aspect-video w-full overflow-hidden rounded-xl bg-black"></div>
        <p class="mt-4 text-center text-sm text-white">Enfoca el código de barras en el centro de la cámara</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2/dist/quagga.min.js"></script>

<script>
let scannerActive = false;

function openQuickProductModal() {
    document.getElementById('quick-product-modal').classList.remove('hidden');
    document.getElementById('quick-product-modal').classList.add('flex');
    document.getElementById('quick-name').focus();
}

function closeQuickProductModal() {
    stopBarcodeScanner();
    document.getElementById('quick-product-modal').classList.add('hidden');
    document.getElementById('quick-product-modal').classList.remove('flex');
    document.getElementById('quick-product-form').reset();
    document.getElementById('quick-inventory-fields').classList.add('hidden');
}

function startBarcodeScanner() {
    if (scannerActive) return;

    const cameraModal = document.getElementById('camera-modal');
    cameraModal.classList.remove('hidden');
    cameraModal.classList.add('flex');

    scannerActive = true;

    Quagga.init({
        inputStream: {
            name: "Live",
            type: "LiveStream",
            target: document.querySelector('#barcode-scanner'),
            constraints: {
                facingMode: "environment",
                width: { min: 640, ideal: 1280 },
                height: { min: 480, ideal: 720 }
            },
        },
        decoder: {
            readers: [
                "ean_reader",
                "ean_8_reader",
                "code_128_reader",
                "code_39_reader",
                "upc_reader",
                "upc_e_reader"
            ]
        },
        locate: true,
        locator: {
            patchSize: "medium",
            halfSample: true
        },
        numOfWorkers: 4,
        frequency: 10,
    }, function(err) {
        if (err) {
            console.error('Error iniciando escáner:', err);
            alert('No se pudo acceder a la cámara. Verifica los permisos.');
            stopBarcodeScanner();
            return;
        }
        Quagga.start();
    });

    Quagga.onDetected(function(result) {
        if (result && result.codeResult && result.codeResult.code) {
            const code = result.codeResult.code;
            document.getElementById('quick-barcode').value = code;

            // Autocompletar con OpenFoodFacts
            fetchProductDataFromBarcode(code);

            stopBarcodeScanner();
        }
    });
}

function stopBarcodeScanner() {
    if (scannerActive) {
        Quagga.stop();
        scannerActive = false;
    }
    document.getElementById('camera-modal').classList.add('hidden');
    document.getElementById('camera-modal').classList.remove('flex');
}

async function fetchProductDataFromBarcode(barcode) {
    try {
        const response = await fetch(`https://world.openfoodfacts.org/api/v0/product/${barcode}.json`);
        const data = await response.json();

        if (data.status === 1 && data.product) {
            const product = data.product;

            if (product.product_name) {
                document.getElementById('quick-name').value = product.product_name;
            }

            if (product.brands) {
                document.getElementById('quick-brand').value = product.brands.split(',')[0].trim();
            }
        }
    } catch (error) {
        console.error('Error obteniendo datos del producto:', error);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const addInventoryCheckbox = document.getElementById('quick-add-inventory');
    const inventoryFields = document.getElementById('quick-inventory-fields');
    const submitText = document.getElementById('quick-submit-text');

    addInventoryCheckbox?.addEventListener('change', (e) => {
        if (e.target.checked) {
            inventoryFields.classList.remove('hidden');
            submitText.textContent = 'Guardar y agregar a inventario';
        } else {
            inventoryFields.classList.add('hidden');
            submitText.textContent = 'Guardar';
        }
    });

    // Submit handler
    document.getElementById('quick-product-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Guardando...';

        try {
            const response = await fetch('{{ route("food.products.quick-store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                closeQuickProductModal();
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            } else {
                let errorMsg = 'Error al guardar el producto';
                if (data.errors) {
                    const errors = Object.values(data.errors).flat();
                    errorMsg = errors.join('\n');
                } else if (data.message) {
                    errorMsg = data.message;
                }
                alert(errorMsg);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error de conexión. Verifica tu conexión a internet e intenta nuevamente.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });

    // Barcode scanner button
    document.getElementById('quick-scan-barcode')?.addEventListener('click', () => {
        startBarcodeScanner();
    });

    // También permitir escaneo con Enter en el campo de código
    document.getElementById('quick-barcode')?.addEventListener('change', async (e) => {
        const barcode = e.target.value.trim();
        if (barcode.length >= 8) {
            await fetchProductDataFromBarcode(barcode);
        }
    });
});
</script>
