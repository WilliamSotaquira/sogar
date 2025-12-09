/**
 * Barcode Scanner Component
 * Reutilizable para escanear códigos de barras en diferentes vistas
 */

export class BarcodeScanner {
    constructor(options = {}) {
        this.targetInput = options.targetInput; // Input donde se colocará el código
        this.onScan = options.onScan || null; // Callback al escanear
        this.formats = options.formats || [
            'ean_13', 'ean_8', 'upc_a', 'upc_e',
            'code_128', 'code_39', 'qr_code'
        ];

        this.stream = null;
        this.scannerActive = false;
        this.modal = null;
        this.video = null;
        this.statusDiv = null;

        this.init();
    }

    init() {
        // Crear modal si no existe
        if (!document.getElementById('barcode-scanner-modal')) {
            this.createModal();
        } else {
            this.modal = document.getElementById('barcode-scanner-modal');
            this.video = document.getElementById('barcode-video');
            this.statusDiv = document.getElementById('scanner-status');
        }

        this.attachEvents();
    }

    createModal() {
        const modalHTML = `
            <div id="barcode-scanner-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4">
                <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Escanear código de barras</h3>
                        <button type="button" id="close-scanner-btn" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="relative overflow-hidden rounded-xl bg-black">
                        <video id="barcode-video" class="w-full" playsinline></video>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="h-48 w-full max-w-md border-2 border-emerald-500 bg-transparent opacity-50"></div>
                        </div>
                        <div id="scanner-status" class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-lg bg-black/75 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm">
                            Preparando cámara...
                        </div>
                    </div>

                    <div class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Instrucciones:</strong> Coloca el código de barras dentro del área marcada. El escaneo es automático.
                        </p>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('barcode-scanner-modal');
        this.video = document.getElementById('barcode-video');
        this.statusDiv = document.getElementById('scanner-status');
    }

    attachEvents() {
        const closeScannerBtn = document.getElementById('close-scanner-btn');

        closeScannerBtn?.addEventListener('click', () => this.close());
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.close();
        });

        // Limpiar al salir de la página
        window.addEventListener('beforeunload', () => this.stopScanner());
    }

    async open() {
        this.modal.classList.remove('hidden');
        this.modal.classList.add('flex');
        await this.startScanner();
    }

    close() {
        this.modal.classList.add('hidden');
        this.modal.classList.remove('flex');
        this.stopScanner();
    }

    async startScanner() {
        try {
            this.statusDiv.textContent = 'Iniciando cámara...';

            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });

            this.video.srcObject = this.stream;
            await this.video.play();

            this.statusDiv.textContent = 'Coloca el código de barras en el área marcada';
            this.scannerActive = true;

            // Usar BarcodeDetector API si está disponible
            if ('BarcodeDetector' in window) {
                const barcodeDetector = new BarcodeDetector({
                    formats: this.formats
                });
                this.detectBarcode(barcodeDetector);
            } else {
                // Fallback: usar ZXing library
                this.loadZXingScanner();
            }
        } catch (error) {
            console.error('Error al acceder a la cámara:', error);
            this.statusDiv.textContent = 'Error: No se pudo acceder a la cámara';
            this.statusDiv.classList.add('bg-red-600');
        }
    }

    async detectBarcode(detector) {
        if (!this.scannerActive) return;

        try {
            const barcodes = await detector.detect(this.video);
            if (barcodes.length > 0) {
                const barcode = barcodes[0];
                this.handleBarcodeDetected(barcode.rawValue);
                return;
            }
        } catch (error) {
            console.error('Error al detectar código:', error);
        }

        requestAnimationFrame(() => this.detectBarcode(detector));
    }

    loadZXingScanner() {
        if (window.ZXing) {
            this.startZXingScanner();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/@zxing/library@latest/umd/index.min.js';
        script.onload = () => this.startZXingScanner();
        script.onerror = () => {
            this.statusDiv.textContent = 'Error: No se pudo cargar el escáner';
            this.statusDiv.classList.add('bg-red-600');
        };
        document.head.appendChild(script);
    }

    startZXingScanner() {
        const codeReader = new ZXing.BrowserMultiFormatReader();

        codeReader.decodeFromVideoElement(this.video, (result, error) => {
            if (result) {
                this.handleBarcodeDetected(result.text);
            }
        });
    }

    handleBarcodeDetected(code) {
        this.statusDiv.textContent = `✓ Código detectado: ${code}`;
        this.statusDiv.classList.remove('bg-black/75');
        this.statusDiv.classList.add('bg-emerald-600');

        // Insertar en el input objetivo si existe
        if (this.targetInput) {
            this.targetInput.value = code;

            // Disparar evento change para que otros listeners lo detecten
            this.targetInput.dispatchEvent(new Event('input', { bubbles: true }));
            this.targetInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Ejecutar callback si existe
        if (this.onScan && typeof this.onScan === 'function') {
            this.onScan(code);
        }

        // Cerrar después de 1 segundo
        setTimeout(() => {
            this.close();
            if (this.targetInput) {
                this.targetInput.focus();
            }
        }, 1000);
    }

    stopScanner() {
        this.scannerActive = false;

        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }

        this.video.srcObject = null;
        this.statusDiv.textContent = 'Preparando cámara...';
        this.statusDiv.classList.remove('bg-emerald-600', 'bg-red-600');
        this.statusDiv.classList.add('bg-black/75');
    }
}

// Helper function para agregar botón de escaneo a un input
export function addScannerButton(input, options = {}) {
    const wrapper = document.createElement('div');
    wrapper.className = 'relative flex-1';

    // Mover el input dentro del wrapper
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    // Agregar padding al input para el botón
    input.classList.add('pr-10');

    // Crear botón
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'absolute right-1 top-1 flex h-8 w-8 items-center justify-center rounded-md bg-gray-100 text-gray-600 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600';
    button.title = 'Escanear código de barras';
    button.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
        </svg>
    `;

    wrapper.appendChild(button);

    // Crear scanner instance
    const scanner = new BarcodeScanner({
        targetInput: input,
        ...options
    });

    // Attach click event
    button.addEventListener('click', () => scanner.open());

    return scanner;
}
