/**
 * Barcode Scanner Component
 * Reutilizable para escanear códigos de barras en diferentes vistas
 */

export class BarcodeScanner {
    constructor(options = {}) {
        this.targetInput = options.targetInput; // Input donde se colocará el código
        this.onScan = options.onScan || null; // Callback al escanear
        // Formatos para BarcodeDetector API (sin guiones bajos)
        this.formats = options.formats || [
            'ean_13', 'ean_8', 'upc_a', 'upc_e',
            'code_128', 'code_39', 'qr_code'
        ];

        this.stream = null;
        this.scannerActive = false;
        this.modal = null;
        this.video = null;
        this.canvas = null;
        this.statusDiv = null;
        this.zxingReader = null;
        this.torchBtn = null;
        this.torchEnabled = false;
        this.quaggaActive = false;
        this.currentZoom = 1.0;
        this.zoomInBtn = null;
        this.zoomOutBtn = null;
        this.zoomLevelDiv = null;
        this.hasOpticalZoom = false;
        this.minZoom = 1;
        this.maxZoom = 1;

        // Detectar si es dispositivo móvil
        this.isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        console.log('BarcodeScanner: Dispositivo móvil:', this.isMobile);

        this.init();
    }

    init() {
        // Crear modal si no existe
        if (!document.getElementById('barcode-scanner-modal')) {
            this.createModal();
        } else {
            this.modal = document.getElementById('barcode-scanner-modal');
            this.video = document.getElementById('barcode-video');
            this.canvas = document.getElementById('barcode-canvas');
            this.statusDiv = document.getElementById('scanner-status');
            this.torchBtn = document.getElementById('toggle-torch-btn');
            this.zoomInBtn = document.getElementById('zoom-in-btn');
            this.zoomOutBtn = document.getElementById('zoom-out-btn');
            this.zoomLevelDiv = document.getElementById('zoom-level');
        }

        this.attachEvents();
    }

    createModal() {
        // Detectar móvil para instrucciones personalizadas
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        const modalHTML = `
            <div id="barcode-scanner-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4">
                <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Escanear código de barras</h3>
                        <div class="flex gap-2">
                            <button type="button" id="zoom-in-btn" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" title="Acercar zoom">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                                </svg>
                            </button>
                            <button type="button" id="zoom-out-btn" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" title="Alejar zoom">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                                </svg>
                            </button>
                            <button type="button" id="toggle-torch-btn" class="hidden rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" title="Activar/Desactivar linterna">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </button>
                            <button type="button" id="close-scanner-btn" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="relative overflow-hidden rounded-xl bg-black">
                        <video id="barcode-video" class="w-full" playsinline autoplay muted style="transform: scale(1); transform-origin: center;"></video>
                        <canvas id="barcode-canvas" class="hidden"></canvas>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="relative h-32 w-64">
                                <!-- Marco de escaneo más pequeño y centrado -->
                                <div class="absolute top-0 left-0 h-10 w-10 border-t-4 border-l-4 border-emerald-400 rounded-tl-lg"></div>
                                <div class="absolute top-0 right-0 h-10 w-10 border-t-4 border-r-4 border-emerald-400 rounded-tr-lg"></div>
                                <div class="absolute bottom-0 left-0 h-10 w-10 border-b-4 border-l-4 border-emerald-400 rounded-bl-lg"></div>
                                <div class="absolute bottom-0 right-0 h-10 w-10 border-b-4 border-r-4 border-emerald-400 rounded-br-lg"></div>
                                <!-- Línea de escaneo -->
                                <div class="absolute inset-x-0 top-1/2 h-1 bg-gradient-to-r from-transparent via-emerald-400 to-transparent opacity-75 shadow-lg shadow-emerald-500/50 animate-pulse"></div>
                            </div>
                        </div>
                        <div id="scanner-status" class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-lg bg-black/75 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm text-center">
                            Preparando cámara...
                        </div>
                        <div id="zoom-level" class="absolute top-4 right-4 rounded-lg bg-black/75 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                            Zoom: 1.0x
                        </div>
                    </div>

                    <div class="mt-4 rounded-lg bg-gradient-to-r from-emerald-50 to-teal-50 p-4 dark:from-gray-800 dark:to-gray-900">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            ${isMobile ? '📱 Instrucciones para móvil:' : '💡 Instrucciones:'}
                        </p>
                        <ul class="text-xs text-gray-700 dark:text-gray-300 space-y-1.5 ml-4 list-disc">
                            ${isMobile ? `
                            <li><strong>Distancia:</strong> Mantén el código a <strong>10-15 cm</strong> de la cámara</li>
                            <li><strong>Zoom opcional:</strong> Si necesitas, usa los botones + / - para acercar</li>
                            <li><strong>Código centrado:</strong> Alinea el código dentro del marco verde</li>
                            <li><strong>Luz:</strong> Asegúrate de tener buena iluminación 💡</li>
                            <li><strong>Enfoque:</strong> Toca la pantalla si la imagen está borrosa</li>
                            ` : `
                            <li><strong>Zoom:</strong> Usa los botones + / - para acercar el código</li>
                            <li><strong>Distancia:</strong> Mantén el código a <strong>20-30 cm</strong> de la cámara</li>
                            <li><strong>Código centrado:</strong> El código debe estar dentro del marco verde</li>
                            <li><strong>Buena luz:</strong> Evita sombras, usa la linterna 💡 si es necesario</li>
                            `}
                        </ul>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('barcode-scanner-modal');
        this.video = document.getElementById('barcode-video');
        this.canvas = document.getElementById('barcode-canvas');
        this.statusDiv = document.getElementById('scanner-status');
        this.torchBtn = document.getElementById('toggle-torch-btn');
    }

    attachEvents() {
        const closeScannerBtn = document.getElementById('close-scanner-btn');

        closeScannerBtn?.addEventListener('click', () => this.close());
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.close();
        });

        // Tap to focus en el video
        this.video?.addEventListener('click', () => this.attemptFocus());

        // Toggle torch/linterna
        this.torchBtn?.addEventListener('click', () => this.toggleTorch());

        // Zoom buttons
        this.zoomInBtn?.addEventListener('click', () => this.adjustZoom(0.5));
        this.zoomOutBtn?.addEventListener('click', () => this.adjustZoom(-0.5));

        // Limpiar al salir de la página
        window.addEventListener('beforeunload', () => this.stopScanner());
    }

    adjustZoom(delta) {
        if (this.hasOpticalZoom && this.stream) {
            // Zoom óptico (real de la cámara)
            const newZoom = Math.max(this.minZoom, Math.min(this.maxZoom, this.currentZoom + delta));

            if (newZoom === this.currentZoom) return;

            const track = this.stream.getVideoTracks()[0];

            track.applyConstraints({
                advanced: [{ zoom: newZoom }]
            }).then(() => {
                this.currentZoom = newZoom;
                console.log('BarcodeScanner: Zoom óptico ajustado a', this.currentZoom.toFixed(2) + 'x');

                if (this.zoomLevelDiv) {
                    this.zoomLevelDiv.textContent = `Zoom: ${this.currentZoom.toFixed(1)}x`;
                }

                this.updateDistanceMessage();
            }).catch(error => {
                console.error('BarcodeScanner: Error al ajustar zoom óptico:', error);
            });
        } else {
            // Zoom digital (CSS transform)
            this.currentZoom = Math.max(1.0, Math.min(4.0, this.currentZoom + delta));

            if (this.video) {
                this.video.style.transform = `scale(${this.currentZoom})`;
                console.log('BarcodeScanner: Zoom digital ajustado a', this.currentZoom.toFixed(1) + 'x');
            }

            if (this.zoomLevelDiv) {
                this.zoomLevelDiv.textContent = `Zoom: ${this.currentZoom.toFixed(1)}x (digital)`;
            }

            this.updateDistanceMessage();
        }
    }

    updateDistanceMessage() {
        if (this.statusDiv) {
            const distance = this.currentZoom > 1.8 ? '20-30cm' : '15-20cm';
            this.statusDiv.innerHTML = `🔍 Escaneando (${distance})<br><small>Código dentro del marco verde</small>`;
        }
    }

    async toggleTorch() {
        if (!this.stream) return;

        try {
            const track = this.stream.getVideoTracks()[0];
            const capabilities = track.getCapabilities();

            if (capabilities.torch) {
                this.torchEnabled = !this.torchEnabled;
                await track.applyConstraints({
                    advanced: [{ torch: this.torchEnabled }]
                });

                console.log('BarcodeScanner: Linterna', this.torchEnabled ? 'ACTIVADA' : 'DESACTIVADA');

                // Feedback visual
                if (this.torchEnabled) {
                    this.torchBtn.classList.add('bg-yellow-500', 'text-white');
                    this.torchBtn.classList.remove('text-gray-500');
                } else {
                    this.torchBtn.classList.remove('bg-yellow-500', 'text-white');
                    this.torchBtn.classList.add('text-gray-500');
                }
            }
        } catch (error) {
            console.log('BarcodeScanner: No se pudo controlar la linterna:', error);
        }
    }

    async attemptFocus() {
        if (!this.stream) return;

        try {
            const track = this.stream.getVideoTracks()[0];
            const capabilities = track.getCapabilities();

            // Intentar enfocar manualmente
            if (capabilities.focusMode) {
                await track.applyConstraints({
                    advanced: [{ focusMode: 'single-shot' }]
                });
                console.log('BarcodeScanner: Enfoque manual aplicado');

                // Feedback visual
                this.statusDiv.textContent = '🎯 Enfocando...';
                setTimeout(() => {
                    this.statusDiv.innerHTML = 'Coloca el código de barras en el área marcada<br><small>Mantén el código a 15-20cm de la cámara</small>';
                }, 500);
            }
        } catch (error) {
            console.log('BarcodeScanner: No se pudo aplicar enfoque manual:', error);
        }
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
            console.log('BarcodeScanner: Iniciando cámara...');

            // Configuración optimizada para lectura de códigos de barras
            const constraints = {
                video: {
                    facingMode: 'environment',
                    // Resolución más baja en móviles para mejor rendimiento y menor distancia
                    width: { ideal: this.isMobile ? 1280 : 1920 },
                    height: { ideal: this.isMobile ? 720 : 1080 },
                    focusMode: { ideal: 'continuous' },
                    // Distancia de enfoque más cercana en móviles
                    focusDistance: { ideal: this.isMobile ? 0.15 : 0.2 }, // 15cm en móvil, 20cm en escritorio
                    whiteBalanceMode: { ideal: 'continuous' },
                    exposureMode: { ideal: 'continuous' },
                    brightness: { ideal: 1.2 },
                    contrast: { ideal: 1.3 },
                    saturation: { ideal: 1.1 }
                }
            };

            this.stream = await navigator.mediaDevices.getUserMedia(constraints);

            this.video.srcObject = this.stream;
            await this.video.play();

            // Esperar a que el video esté realmente reproduciendo
            await new Promise(resolve => {
                if (this.video.readyState >= 2) {
                    resolve();
                } else {
                    this.video.addEventListener('loadeddata', resolve, { once: true });
                }
            });

            console.log('BarcodeScanner: Video listo - Dimensiones:', this.video.videoWidth, 'x', this.video.videoHeight);

            // Optimizar configuración de la cámara
            const track = this.stream.getVideoTracks()[0];
            const capabilities = track.getCapabilities();
            const settings = track.getSettings();

            console.log('BarcodeScanner: Capacidades disponibles:', capabilities);
            console.log('BarcodeScanner: Configuración actual:', settings);

            // Aplicar optimizaciones si están disponibles
            const optimizedConstraints = {};

            // Enfoque continuo para códigos de barras
            if (capabilities.focusMode && capabilities.focusMode.includes('continuous')) {
                optimizedConstraints.focusMode = 'continuous';
            }

            // Distancia de enfoque óptima (cerca, para códigos de barras)
            if (capabilities.focusDistance) {
                optimizedConstraints.focusDistance = capabilities.focusDistance.min || 0.1;
            }

            // Brillo aumentado
            if (capabilities.brightness) {
                const midBrightness = (capabilities.brightness.min + capabilities.brightness.max) / 2;
                optimizedConstraints.brightness = Math.min(midBrightness * 1.3, capabilities.brightness.max);
            }

            // Contraste aumentado para mejor definición
            if (capabilities.contrast) {
                const midContrast = (capabilities.contrast.min + capabilities.contrast.max) / 2;
                optimizedConstraints.contrast = Math.min(midContrast * 1.4, capabilities.contrast.max);
            }

            // Exposición automática continua
            if (capabilities.exposureMode && capabilities.exposureMode.includes('continuous')) {
                optimizedConstraints.exposureMode = 'continuous';
            }

            // Balance de blancos automático
            if (capabilities.whiteBalanceMode && capabilities.whiteBalanceMode.includes('continuous')) {
                optimizedConstraints.whiteBalanceMode = 'continuous';
            }

            // Aplicar optimizaciones
            if (Object.keys(optimizedConstraints).length > 0) {
                try {
                    await track.applyConstraints({ advanced: [optimizedConstraints] });
                    console.log('BarcodeScanner: ✓ Optimizaciones aplicadas:', optimizedConstraints);
                } catch (e) {
                    console.log('BarcodeScanner: No se pudieron aplicar algunas optimizaciones:', e.message);
                }
            }

            // Habilitar botón de linterna si está disponible
            if (capabilities.torch && this.torchBtn) {
                this.torchBtn.classList.remove('hidden');
                console.log('BarcodeScanner: ✓ Linterna disponible');
            }

            // Aplicar zoom inicial automático si está disponible
            if (capabilities.zoom) {
                const minZoom = capabilities.zoom.min || 1;
                const maxZoom = capabilities.zoom.max || 1;

                // En móviles: zoom más bajo (20% del rango) o ninguno
                // En escritorio: zoom medio (40% del rango)
                const zoomPercent = this.isMobile ? 0.15 : 0.4;
                const initialZoom = minZoom + (maxZoom - minZoom) * zoomPercent;

                try {
                    await track.applyConstraints({
                        advanced: [{ zoom: initialZoom }]
                    });
                    this.currentZoom = initialZoom;
                    console.log('BarcodeScanner: ✓ Zoom óptico aplicado:', initialZoom.toFixed(2) + 'x (rango:', minZoom, '-', maxZoom + ')');

                    if (this.zoomLevelDiv) {
                        this.zoomLevelDiv.textContent = `Zoom: ${initialZoom.toFixed(1)}x`;
                    }
                } catch (e) {
                    console.log('BarcodeScanner: No se pudo aplicar zoom inicial:', e.message);
                }

                // Guardar capacidades de zoom
                this.minZoom = minZoom;
                this.maxZoom = maxZoom;
                this.hasOpticalZoom = true;
                console.log('BarcodeScanner: Zoom óptico disponible - usa los botones +/- para ajustar');
            } else {
                console.log('BarcodeScanner: Zoom óptico no disponible, usando zoom digital (CSS)');
                this.hasOpticalZoom = false;
                // En móviles: zoom digital más bajo (1.3x) o ninguno
                // En escritorio: zoom digital medio (2.0x)
                this.currentZoom = this.isMobile ? 1.3 : 2.0;
                if (this.video) {
                    this.video.style.transform = `scale(${this.currentZoom})`;
                }
                if (this.zoomLevelDiv) {
                    this.zoomLevelDiv.textContent = `Zoom: ${this.currentZoom.toFixed(1)}x (digital)`;
                }
            }

            const distanceMsg = this.isMobile ? '10-15cm' : '20-25cm';
            this.statusDiv.innerHTML = `Escaneando...<br><small>Código a ${distanceMsg} de la cámara</small>`;
            this.scannerActive = true;

            // Usar Quagga.js (más efectivo para códigos de barras)
            console.log('BarcodeScanner: Cargando Quagga.js...');
            this.loadZXingScanner(); // Este método ahora carga Quagga primero

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
                console.log('BarcodeScanner: ✓✓✓ CÓDIGO DETECTADO:', barcode.rawValue, 'Formato:', barcode.format);
                this.handleBarcodeDetected(barcode.rawValue);
                return;
            }
        } catch (error) {
            // No mostrar error si es solo que no detectó nada
            if (error.message && !error.message.includes('could not be detected')) {
                console.error('BarcodeScanner: Error al detectar:', error);
            }
        }

        // Continuar escaneando (más rápido - cada frame)
        requestAnimationFrame(() => this.detectBarcode(detector));
    }

    loadZXingScanner() {
        console.log('BarcodeScanner: Cargando Quagga.js (mejor para códigos de barras)...');

        if (window.Quagga) {
            console.log('BarcodeScanner: Quagga ya está cargado');
            this.startQuaggaScanner();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/@ericblade/quagga2@1.8.4/dist/quagga.min.js';
        script.onload = () => {
            console.log('BarcodeScanner: ✓ Quagga cargado exitosamente');
            this.startQuaggaScanner();
        };
        script.onerror = () => {
            console.error('BarcodeScanner: Error al cargar Quagga, intentando ZXing...');
            this.loadZXingFallback();
        };
        document.head.appendChild(script);
    }

    loadZXingFallback() {
        console.log('BarcodeScanner: Cargando ZXing como fallback...');

        if (window.ZXing) {
            console.log('BarcodeScanner: ZXing ya está cargado');
            this.startZXingScanner();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/@zxing/library@latest/umd/index.min.js';
        script.onload = () => {
            console.log('BarcodeScanner: ZXing cargado exitosamente');
            this.startZXingScanner();
        };
        script.onerror = () => {
            console.error('BarcodeScanner: Error al cargar ZXing');
            this.statusDiv.textContent = 'Error: No se pudo cargar el escáner';
            this.statusDiv.classList.add('bg-red-600');
        };
        document.head.appendChild(script);
    }

    startQuaggaScanner() {
        if (!this.scannerActive) return;

        console.log('BarcodeScanner: Iniciando Quagga scanner...');
        console.log('BarcodeScanner: Video listo:', this.video.videoWidth, 'x', this.video.videoHeight);

        this.statusDiv.innerHTML = '🔍 Escaneando con Quagga...<br><small>Código dentro del marco verde</small>';

        // Esperar a que el video esté listo
        if (this.video.readyState < 2) {
            console.log('BarcodeScanner: Esperando a que el video esté listo...');
            this.video.addEventListener('loadeddata', () => {
                console.log('BarcodeScanner: Video listo, iniciando Quagga');
                this.initQuagga();
            }, { once: true });
        } else {
            this.initQuagga();
        }
    }

    initQuagga() {
        console.log('BarcodeScanner: Configurando Quagga con alta precisión...');

        // Área de detección más amplia en móviles
        const detectionArea = this.isMobile ? {
            top: "20%",
            right: "10%",
            left: "10%",
            bottom: "20%"
        } : {
            top: "30%",
            right: "20%",
            left: "20%",
            bottom: "30%"
        };

        const config = {
            inputStream: {
                type: "LiveStream",
                target: this.video,
                constraints: {
                    facingMode: "environment",
                    width: { ideal: this.isMobile ? 1280 : 1920 },
                    height: { ideal: this.isMobile ? 720 : 1080 }
                },
                area: detectionArea
            },
            decoder: {
                readers: [
                    "ean_reader"      // Solo EAN-13 para máxima precisión
                ],
                multiple: false
            },
            locate: true,
            locator: {
                patchSize: "x-large",  // Parches extra grandes
                halfSample: false      // Sin submuestreo
            },
            frequency: 5,  // Reducir frecuencia para mejor procesamiento
            numOfWorkers: 0
        };

        console.log('BarcodeScanner: Inicializando Quagga con validación estricta...');

        Quagga.init(config, (err) => {
            if (err) {
                console.error('BarcodeScanner: Error al inicializar Quagga:', err);
                console.log('BarcodeScanner: Intentando con ZXing...');
                this.loadZXingFallback();
                return;
            }

            console.log('BarcodeScanner: ✓ Quagga inicializado correctamente');
            Quagga.start();
            console.log('BarcodeScanner: ✓ Quagga escaneando activamente');
        });

        // Almacenar detecciones para validación con timestamp
        let detectionHistory = [];
        // En móviles: requisitos más relajados para compensar menor precisión de cámara
        const REQUIRED_MATCHES = this.isMobile ? 3 : 5; // 3 en móvil, 5 en escritorio
        const HISTORY_SIZE = 10;
        const MIN_QUALITY = this.isMobile ? 0.75 : 0.85; // 75% en móvil, 85% en escritorio

        // Listener para códigos detectados con validación estricta
        Quagga.onDetected((result) => {
            if (!this.scannerActive) return;

            if (result && result.codeResult && result.codeResult.code) {
                const code = result.codeResult.code;
                const format = result.codeResult.format;

                // Validar que sea EAN-13 (13 dígitos)
                if (code.length !== 13 || !/^\d{13}$/.test(code)) {
                    console.log(`BarcodeScanner: ⚠️ Código inválido (debe ser 13 dígitos): ${code}`);
                    return;
                }

                // Calcular confianza promedio
                let totalError = 0;
                let errorCount = 0;

                if (result.codeResult.decodedCodes) {
                    result.codeResult.decodedCodes.forEach(decoded => {
                        if (decoded.error !== undefined) {
                            totalError += decoded.error;
                            errorCount++;
                        }
                    });
                }

                const avgError = errorCount > 0 ? totalError / errorCount : 1;
                const quality = 1 - avgError;

                console.log(`BarcodeScanner: "${code}" - Calidad: ${(quality * 100).toFixed(1)}%`);

                // Solo aceptar lecturas con calidad muy alta
                if (quality < MIN_QUALITY) {
                    console.log(`BarcodeScanner: ⚠️ Calidad insuficiente (requiere >${(MIN_QUALITY*100).toFixed(0)}%)`);
                    return;
                }

                // Agregar a historial con timestamp
                const now = Date.now();
                detectionHistory.push({ code, quality, time: now });

                // Limpiar detecciones antiguas (más de 3 segundos)
                detectionHistory = detectionHistory.filter(d => now - d.time < 3000);

                // Mantener tamaño máximo
                if (detectionHistory.length > HISTORY_SIZE) {
                    detectionHistory.shift();
                }

                // Contar coincidencias del mismo código
                const matches = detectionHistory.filter(d => d.code === code).length;
                const avgQuality = detectionHistory
                    .filter(d => d.code === code)
                    .reduce((sum, d) => sum + d.quality, 0) / matches;

                console.log(`BarcodeScanner: Coincidencias: ${matches}/${REQUIRED_MATCHES} - Calidad promedio: ${(avgQuality * 100).toFixed(1)}%`);

                if (matches >= REQUIRED_MATCHES) {
                    console.log(`BarcodeScanner: ✓✓✓ CÓDIGO VALIDADO: ${code}`);
                    console.log(`BarcodeScanner: Calidad final: ${(avgQuality * 100).toFixed(1)}%`);

                    // Detener Quagga
                    Quagga.stop();
                    this.quaggaActive = false;
                    detectionHistory = [];

                    this.handleBarcodeDetected(code);
                } else {
                    this.statusDiv.innerHTML = `🔍 Validando...<br><small>${matches}/${REQUIRED_MATCHES} (${(avgQuality * 100).toFixed(0)}% calidad)</small>`;
                }
            }
        });

        // Almacenar para poder detenerlo después
        this.quaggaActive = true;
    }

    startZXingScanner() {
        if (!this.scannerActive) return;

        console.log('BarcodeScanner: Iniciando ZXing scanner...');

        try {
            const codeReader = new ZXing.BrowserMultiFormatReader();

            console.log('BarcodeScanner: ZXing reader creado');
            this.statusDiv.innerHTML = '🔍 Buscando código...<br><small>Mantén el código centrado y a 15-20cm</small>';

            // Almacenar el reader para poder detenerlo después
            this.zxingReader = codeReader;

            // Iniciar decodificación continua
            codeReader.decodeFromVideoDevice(null, this.video, (result, error) => {
                if (result) {
                    console.log('BarcodeScanner: ✓✓✓ ZXing detectó código:', result.text, 'Formato:', result.format);

                    // Detener el scanner antes de manejar el resultado
                    if (this.zxingReader) {
                        this.zxingReader.reset();
                    }

                    this.handleBarcodeDetected(result.text);
                }

                // Solo mostrar errores que no sean "not found"
                if (error && error.name !== 'NotFoundException') {
                    console.error('BarcodeScanner: Error ZXing:', error.name, error.message);
                }
            });

            console.log('BarcodeScanner: ✓ ZXing scanner activo y escaneando');

        } catch (error) {
            console.error('BarcodeScanner: Error al iniciar ZXing:', error);
            this.statusDiv.textContent = 'Error al iniciar el escáner';
            this.statusDiv.classList.add('bg-red-600');
        }
    }

    handleBarcodeDetected(code) {
        console.log('BarcodeScanner: Procesando código detectado:', code);
        console.log('BarcodeScanner: targetInput existe?', !!this.targetInput);
        console.log('BarcodeScanner: targetInput value actual:', this.targetInput?.value);

        this.statusDiv.textContent = `✓ Código detectado: ${code}`;
        this.statusDiv.classList.remove('bg-black/75');
        this.statusDiv.classList.add('bg-emerald-600');

        // Insertar en el input objetivo si existe
        if (this.targetInput) {
            console.log('BarcodeScanner: ✓ Insertando código en input');
            this.targetInput.value = code;
            console.log('BarcodeScanner: ✓ Valor insertado:', this.targetInput.value);

            // Disparar evento change para que otros listeners lo detecten
            console.log('BarcodeScanner: ✓ Disparando evento input');
            this.targetInput.dispatchEvent(new Event('input', { bubbles: true }));
            console.log('BarcodeScanner: ✓ Disparando evento change');
            this.targetInput.dispatchEvent(new Event('change', { bubbles: true }));
            console.log('BarcodeScanner: ✓ Eventos disparados correctamente');
        } else {
            console.error('BarcodeScanner: ❌ targetInput NO EXISTE - No se puede insertar código');
        }

        // Ejecutar callback si existe
        if (this.onScan && typeof this.onScan === 'function') {
            console.log('BarcodeScanner: Ejecutando callback onScan');
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

        // Detener Quagga si está activo
        if (this.quaggaActive && window.Quagga) {
            try {
                Quagga.stop();
                console.log('BarcodeScanner: Quagga detenido');
            } catch (e) {
                console.log('BarcodeScanner: Error al detener Quagga:', e);
            }
            this.quaggaActive = false;
        }

        // Detener ZXing si está activo
        if (this.zxingReader) {
            try {
                this.zxingReader.reset();
                console.log('BarcodeScanner: ZXing reader detenido');
            } catch (e) {
                console.log('BarcodeScanner: Error al detener ZXing:', e);
            }
            this.zxingReader = null;
        }

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
