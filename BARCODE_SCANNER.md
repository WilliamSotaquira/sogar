# Barcode Scanner Component

Componente reutilizable para escanear códigos de barras usando la cámara del dispositivo.

## Características

- ✅ Detección automática de códigos de barras
- ✅ Soporte para múltiples formatos (EAN-13, EAN-8, UPC, Code-128, Code-39, QR)
- ✅ API nativa BarcodeDetector + fallback a ZXing
- ✅ Diseño responsive y dark mode
- ✅ Modal reutilizable
- ✅ Fácil integración

## Uso Básico

### Opción 1: Usar BarcodeScanner directamente

```javascript
// En tu vista blade, después de DOMContentLoaded
const productInput = document.getElementById('product-input');
const scanBtn = document.getElementById('scan-btn');

if (window.BarcodeScanner) {
    const scanner = new window.BarcodeScanner({
        targetInput: productInput,
        onScan: (code) => {
            console.log('Código escaneado:', code);
            // Código ya insertado en el input automáticamente
        }
    });
    
    scanBtn.addEventListener('click', () => scanner.open());
}
```

### Opción 2: Usar addScannerButton helper

```javascript
// Agrega automáticamente un botón de escaneo dentro del input
const productInput = document.getElementById('product-input');

if (window.addScannerButton) {
    window.addScannerButton(productInput, {
        onScan: (code) => {
            console.log('Escaneado:', code);
        }
    });
}
```

## Estructura HTML Recomendada

### Con botón separado:
```html
<div class="flex gap-2">
    <input type="text" id="product-input" placeholder="Producto o código de barras" class="h-10 flex-1 rounded-lg border px-3">
    <button type="button" id="scan-btn" class="h-10 rounded-lg bg-emerald-600 px-4 text-white">
        📷 Escanear
    </button>
</div>
```

### Con botón integrado (usando addScannerButton):
```html
<!-- El helper convierte esto -->
<input type="text" id="product-input" placeholder="Producto" class="h-10 rounded-lg border px-3">

<!-- En esto -->
<div class="relative flex-1">
    <input type="text" id="product-input" placeholder="Producto" class="h-10 rounded-lg border px-3 pr-10">
    <button type="button" class="absolute right-1 top-1 h-8 w-8 rounded-md bg-gray-100">
        <svg><!-- icono barcode --></svg>
    </button>
</div>
```

## Opciones de Configuración

```javascript
new BarcodeScanner({
    targetInput: element,           // Input donde se insertará el código
    onScan: function(code) {},      // Callback al detectar código
    formats: [                       // Formatos soportados (opcional)
        'ean_13',
        'ean_8', 
        'upc_a',
        'upc_e',
        'code_128',
        'code_39',
        'qr_code'
    ]
})
```

## Vistas Implementadas

- ✅ `/food/shopping-list/{id}` - Agregar productos a lista
- ✅ `/food/shopping-list` - Búsqueda de productos en generador de listas
- ✅ `/food/inventory` - Búsqueda y resaltado de productos en inventario

## Agregar a Nueva Vista

1. Asegúrate de que el input tenga un ID único
2. Agrega un botón con ID único o usa el helper
3. Inicializa el scanner en el DOMContentLoaded

Ejemplo completo:
```blade
{{-- En tu vista blade --}}
<form>
    <input type="text" id="my-product-input" name="product" class="...">
    <button type="button" id="my-scan-btn" class="...">Escanear</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('my-product-input');
    const btn = document.getElementById('my-scan-btn');
    
    if (input && btn && window.BarcodeScanner) {
        const scanner = new window.BarcodeScanner({
            targetInput: input
        });
        btn.addEventListener('click', () => scanner.open());
    }
});
</script>
```

## Requisitos

- Navegador moderno con getUserMedia
- HTTPS o localhost (requerido por la API de cámara)
- Permiso de cámara del usuario

## Formatos Soportados

- **EAN-13**: Productos retail europeos (13 dígitos)
- **EAN-8**: Productos pequeños (8 dígitos)
- **UPC-A/E**: Productos USA (12/8 dígitos)
- **Code-128**: Logística y empaquetado
- **Code-39**: Industria y militar
- **QR Code**: Datos bidimensionales

## Troubleshooting

### "Error: No se pudo acceder a la cámara"
- Verifica que estés en HTTPS o localhost
- Comprueba permisos de cámara en el navegador
- Prueba con otro dispositivo o navegador

### "El escaneo no detecta el código"
- Asegura buena iluminación
- Coloca el código dentro del área marcada
- Mantén la cámara estable
- Limpia la lente de la cámara

### "Navegador no compatible"
- El componente carga automáticamente ZXing como fallback
- Actualiza a la última versión del navegador
- Prueba con Chrome, Safari o Edge
