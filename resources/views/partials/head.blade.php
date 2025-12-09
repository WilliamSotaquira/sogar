<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<script>
    // Avoid FOUC by setting theme class ASAP
    (() => {
        const stored = localStorage.getItem('color-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (stored === 'dark' || (!stored && prefersDark)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();
</script>

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
<link rel="apple-touch-icon" href="{{ asset('icon_lg.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])

<script>
    window.ensureBarcodeDetector = window.ensureBarcodeDetector || (() => {
        const loadPolyfill = () => {
            if ('BarcodeDetector' in window) {
                return Promise.resolve(true);
            }
            if (window.__barcodeDetectorLoading) {
                return window.__barcodeDetectorLoading;
            }
            window.__barcodeDetectorLoading = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/@undecaf/barcode-detector-polyfill/dist/barcode-detector-polyfill.min.js';
                script.async = true;
                script.onload = () => {
                    if ('BarcodeDetector' in window) {
                        resolve(true);
                    } else {
                        reject(new Error('BarcodeDetector polyfill did not register.'));
                    }
                };
                script.onerror = () => reject(new Error('Failed to load BarcodeDetector polyfill.'));
                document.head.appendChild(script);
            }).catch((error) => {
                console.warn('[BarcodeDetector] Polyfill load error', error);
                throw error;
            });
            return window.__barcodeDetectorLoading;
        };
        return loadPolyfill;
    })();
</script>
