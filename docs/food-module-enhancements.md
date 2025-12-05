# Módulo de Alimentos - Especificación de Mejoras Implementadas

## 📋 Resumen Ejecutivo

Se han implementado mejoras significativas al módulo de alimentos siguiendo la especificación solicitada:

1. **Presupuesto obligatorio** para listas de compras
2. **Escaneo inteligente** con información completa de inventario y alertas
3. **Sistema de rendimiento de productos** con índice calculado
4. **Registro de precios** con historial y alertas de cambio
5. **Listas colaborativas** con seguimiento de precios reales

---

## 🔧 Cambios en Base de Datos

### Nueva Migración: `2025_12_06_000600_enhance_shopping_lists_and_products.php`

#### Tabla `sogar_shopping_lists`
**Nuevos campos:**
- `budget_id` (FK a budgets) - **REQUERIDO** al generar lista
- `category_id` (FK a categories) - Opcional
- `actual_total` (decimal) - Total real gastado
- `is_collaborative` (boolean) - Permite edición por múltiples usuarios

#### Tabla `sogar_food_products`
**Nuevos campos:**
- `performance_index` (decimal 0-100) - Índice de rendimiento calculado
- `avg_consumption_rate` (decimal) - Tasa promedio de consumo (unidades/día)
- `last_performance_calc` (date) - Última vez que se calculó el índice

#### Tabla `sogar_food_prices`
**Nuevos campos:**
- `price_change_percent` (decimal) - % de cambio respecto al precio anterior
- `is_price_alert` (boolean) - Indica si generó alerta (cambio >10%)

#### Tabla `sogar_shopping_list_items`
**Nuevos campos:**
- `actual_price` (decimal) - Precio real pagado al marcar como comprado
- `vendor_name` (string) - Proveedor donde se compró
- `checked_at` (timestamp) - Cuándo se marcó como comprado
- `low_stock_alert` (boolean) - Indica si tiene alerta de stock bajo

---

## 📁 Nuevos Servicios

### 1. `ProductPerformanceService`
**Ubicación:** `app/Services/ProductPerformanceService.php`

**Métodos principales:**
```php
calculatePerformanceIndex(FoodProduct $product): float
```
Calcula índice 0-100 considerando:
- ✅ Duración del producto (shelf_life_days)
- ✅ Tasa de desperdicio (batches wasted/expired)
- ✅ Rotación/consumo
- ✅ Volatilidad de precio
- ✅ Frecuencia de desabastecimiento

**Interpretación del índice:**
- **80-100:** Excelente rendimiento (recomendar compra)
- **60-79:** Buen rendimiento
- **40-59:** Rendimiento regular
- **0-39:** Bajo rendimiento (considerar alternativas)

```php
generatePerformanceAlerts(int $userId): array
```
Retorna:
```php
[
    'low_performance' => [...], // Productos con índice <= 40
    'high_performance' => [...] // Productos con índice >= 80
]
```

---

### 2. `PriceChangeService`
**Ubicación:** `app/Services/PriceChangeService.php`

**Métodos principales:**
```php
registerPriceChange(
    FoodProduct $product,
    float $newPrice,
    ?string $vendor,
    string $source = 'manual',
    ?string $note = null
): array
```
Registra precio y calcula:
- Cambio absoluto y porcentual
- Genera alerta si cambio > 10% (arriba) o > 15% (abajo)
- Actualiza historial de precios

```php
getPriceHistory(FoodProduct $product, int $months = 6): array
```
Retorna historial con:
- Fecha, precio, vendor, cambio %
- Precio promedio, mínimo, máximo
- Mejor proveedor (precio promedio más bajo)

```php
comparePricesByVendor(FoodProduct $product): array
```
Compara precios entre diferentes proveedores (últimos 3 meses)

```php
getPriceAlerts(int $userId, int $days = 7): array
```
Obtiene alertas recientes de cambios de precio significativos

---

## 🔄 Cambios en Controladores

### `FoodScanController` - API de Escaneo Mejorada

**Endpoint:** `POST /api/food/scan`

**Nuevos parámetros:**
```json
{
    "code": "7501234567890",
    "name": "Producto opcional", 
    "add_to_list": true,        // Agregar automáticamente a lista activa
    "qty_to_buy": 2             // Cantidad a agregar
}
```

**Respuesta mejorada:**
```json
{
    "found": true,
    "created": false,
    "product": {...},
    "inventory": {
        "current_stock": 5.5,
        "unit": "unit",
        "min_stock": 3,
        "low_stock_alert": false
    },
    "pricing": {
        "last_price": 25.50,
        "vendor": "Superama",
        "captured_on": "2025-12-04",
        "currency": "USD"
    },
    "performance": {
        "index": 85.5,
        "avg_consumption_rate": 0.5
    },
    "alerts": [
        {
            "type": "expiring_soon",
            "message": "1 lote(s) próximos a caducar en los próximos 7 días",
            "severity": "info"
        }
    ],
    "added_to_list": true,      // Si add_to_list=true
    "list_item": {...}
}
```

**Tipos de alertas:**
- `low_stock` - Stock por debajo del mínimo
- `expiring_soon` - Caducidad próxima (7 días)
- `low_performance` - Índice < 40
- `high_performance` - Índice >= 80

---

### `ShoppingListController` - Mejoras

#### Método `generate()`
**Cambio crítico:** Ahora **REQUIERE** `budget_id`

```php
POST /food/shopping-list/generate
{
    "budget_id": 5,              // OBLIGATORIO
    "category_id": 3,            // Opcional (toma del budget si no se pasa)
    "name": "Compra semanal",
    "horizon_days": 7,
    "people_count": 4,
    "safety_factor": 1.2,
    "expected_purchase_on": "2025-12-10"
}
```

**Comportamiento:**
1. Valida que el presupuesto pertenezca al usuario
2. Cierra la lista activa anterior (solo 1 activa a la vez)
3. Genera nueva lista vinculada al presupuesto
4. Asigna categoría del presupuesto si no se especifica

#### Método `markItem()`
**Nuevos parámetros:**
```php
POST /food/shopping-list/{list}/items/{itemId}
{
    "is_checked": true,
    "qty_to_buy_base": 2,
    "actual_price": 51.00,       // Precio REAL pagado
    "vendor_name": "Walmart"
}
```

**Flujo mejorado:**
1. Marca item como comprado
2. Registra `checked_at` timestamp
3. Si hay `actual_price`, lo guarda Y registra en historial de precios
4. Si hay `vendor_name`, lo almacena
5. Crea batch de inventario con precio real (no estimado)
6. **Actualiza `actual_total` de la lista** sumando precios reales

---

## 🎨 Flujo de Usuario Completo

### 1️⃣ Crear/Asignar Presupuesto
```
Usuario → Presupuestos → Crear "Alimentos $5000" mensual
```

### 2️⃣ Generar Lista de Compras
```
Usuario → Alimentos → Listas → Generar nueva
- Selecciona presupuesto "Alimentos $5000" (OBLIGATORIO)
- Sistema genera lista con productos bajo mínimo
- Lista vinculada a presupuesto
```

### 3️⃣ Ajustar Lista (Pre-compra)
```
Usuario puede:
- Agregar productos manualmente
- Eliminar productos
- Ajustar cantidades
- Buscar por nombre/barcode
```

### 4️⃣ Durante Compra - Escaneo Inteligente
```
Móvil → Escanear código
API responde con:
✅ Stock actual: 2 unidades
⚠️ Alerta: Stock bajo (mínimo: 5)
💰 Último precio: $25.50 en Superama
📊 Rendimiento: 85/100 (Excelente)
⏰ Alerta: 1 lote caduca en 5 días

Usuario:
- Ve si debe comprarlo o no
- Si es nuevo, puede crearlo al momento
- Puede agregarlo a la lista con 1 clic
```

### 5️⃣ Marcar como Comprado (Checklist)
```
Usuario en supermercado:
1. Marca item como comprado
2. Ingresa precio real: $27.00
3. Ingresa vendor: "Soriana"

Sistema:
✅ Registra precio en historial
📊 Calcula cambio: +5.9% vs último precio
⚠️ Genera alerta de precio (subió >10%)
📦 Ingresa automáticamente al inventario
💰 Actualiza total de lista
```

### 6️⃣ Finalizar Compra
```
Lista muestra:
- Estimado: $450
- Real gastado: $472
- Diferencia: +$22 (+4.9%)
- Presupuesto disponible: $4,528 / $5,000
```

---

## 📊 Nuevas Funcionalidades - Detalles Técnicos

### Índice de Rendimiento

**Cálculo automático por:**
- Job programado diario (recomendado)
- Manualmente: `ProductPerformanceService::calculatePerformanceIndex()`
- Tras cada compra (opcional)

**Factores que afectan el índice:**

| Factor | Impacto | Puntos |
|--------|---------|--------|
| Duración larga (>180 días) | Positivo | +20 |
| Desperdicio bajo (<5%) | Positivo | +10 |
| Rotación rápida | Positivo | +20 |
| Precio estable (<10% variación) | Positivo | +15 |
| Siempre disponible | Positivo | +5 |
| Desperdicio alto (>30%) | Negativo | -30 |
| Consumo lento vs caducidad | Negativo | -10 |
| Precio volátil (>50%) | Negativo | -10 |

**Uso en dashboard:**
```php
$alerts = $performanceService->generatePerformanceAlerts($userId);

// Para productos con bajo rendimiento
foreach ($alerts['low_performance'] as $alert) {
    echo "{$alert['product']->name}: {$alert['score']}/100";
    echo $alert['message'];
    // "Considera reducir compras o buscar alternativas"
}

// Para productos con alto rendimiento  
foreach ($alerts['high_performance'] as $alert) {
    echo "{$alert['product']->name}: {$alert['score']}/100";
    echo $alert['message'];
    // "Es una buena opción de compra"
}
```

---

### Alertas de Precio

**Generación automática:**
Al registrar un precio, si el cambio es > 10% arriba o > 15% abajo:
```php
$priceChange = $priceService->registerPriceChange(
    $product,
    $newPrice,
    'Walmart',
    'purchase'
);

if ($priceChange['is_alert']) {
    // Mostrar alerta al usuario
    echo $priceChange['alert_message'];
    // ⚠️ El precio de Leche ha subido 12.5% en Walmart (de $25.50 a $28.70)
    // o
    // ✅ El precio de Arroz ha bajado 18% en Soriana (de $45.00 a $36.90)
}
```

**Dashboard de alertas:**
```php
$priceAlerts = $priceService->getPriceAlerts($userId, 7);

foreach ($priceAlerts as $alert) {
    if ($alert['severity'] === 'warning') {
        // Precio subió
        echo "⚠️ {$alert['product']} subió {$alert['change_percent']}% en {$alert['vendor']}";
    } else {
        // Precio bajó
        echo "✅ {$alert['product']} bajó {$alert['change_percent']}% en {$alert['vendor']}";
    }
}
```

---

## 🧪 Testing Sugerido

### Test 1: Generar lista sin presupuesto (debe fallar)
```php
POST /food/shopping-list/generate
{
    "name": "Mi lista"
}
// Espera: 422 - budget_id is required
```

### Test 2: Escaneo inteligente
```php
POST /api/food/scan
{
    "code": "7501234567890",
    "add_to_list": true,
    "qty_to_buy": 2
}
// Espera: 200 + información completa de inventory, pricing, alerts
```

### Test 3: Marcar item con precio real
```php
POST /food/shopping-list/1/items/5
{
    "is_checked": true,
    "actual_price": 55.00,
    "vendor_name": "Costco"
}
// Espera: 
// - Item marcado
// - Precio registrado en historial
// - Inventario actualizado
// - Lista total actualizado
```

### Test 4: Calcular rendimiento
```php
$service = app(ProductPerformanceService::class);
$index = $service->calculatePerformanceIndex($product);
// Espera: float entre 0-100
```

---

## 📱 Uso desde Móvil

### Escaneo Rápido
```javascript
// En app móvil
const response = await fetch('/api/food/scan', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        code: scannedBarcode,
        add_to_list: true,
        qty_to_buy: 1
    })
});

const data = await response.json();

// Mostrar en UI:
if (data.alerts.length > 0) {
    // Mostrar badges de alerta
    data.alerts.forEach(alert => {
        showAlert(alert.message, alert.severity);
    });
}

// Mostrar stock actual
console.log(`Stock: ${data.inventory.current_stock} ${data.inventory.unit}`);

// Último precio
console.log(`Precio: $${data.pricing.last_price} en ${data.pricing.vendor}`);

// Rendimiento
if (data.performance.index >= 80) {
    showBadge('⭐ Excelente producto');
} else if (data.performance.index < 40) {
    showBadge('⚠️ Bajo rendimiento');
}
```

---

## 🔐 Seguridad y Validaciones

### Presupuestos
- ✅ Validar que budget_id pertenece al usuario autenticado
- ✅ Solo 1 lista activa por usuario a la vez
- ✅ No se puede eliminar lista activa

### Listas Colaborativas
- ✅ Flag `is_collaborative` permite acceso multi-usuario (futuro)
- ✅ Actualmente solo el owner puede editar
- 🔜 Implementar sistema de permisos por hogar compartido

### Precios
- ✅ Solo el usuario puede ver su historial de precios
- ✅ Precios se registran con timestamp para auditoría
- ✅ Source tracking: manual, scan, purchase, ticket

---

## 🎯 Próximos Pasos Recomendados

### Corto Plazo (1-2 semanas)
1. ✅ **COMPLETADO:** Migraciones y servicios base
2. 🔄 **UI para generar lista con presupuesto** (formulario)
3. 🔄 **Vista móvil de lista como checklist**
4. 🔄 **Input de precio real al marcar item**
5. 🔄 **Dashboard de alertas de rendimiento**

### Mediano Plazo (1 mes)
6. ⏳ Job diario para calcular rendimiento automáticamente
7. ⏳ Gráficas de historial de precios por producto
8. ⏳ Comparativa de precios entre vendors
9. ⏳ Notificaciones push de alertas
10. ⏳ Exportar lista a PDF/WhatsApp

### Largo Plazo (3 meses)
11. ⏳ Multi-usuario colaborativo (hogares compartidos)
12. ⏳ IA para sugerir mejores días/lugares de compra
13. ⏳ Integración con Google Tasks
14. ⏳ Análisis de tendencias de consumo
15. ⏳ Sugerencias de recetas según inventario

---

## 📄 Documentación API

### Endpoints Actualizados

#### POST `/api/food/scan`
**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**
```json
{
    "code": "string (required)",
    "name": "string (optional)",
    "add_to_list": "boolean (optional, default: false)",
    "qty_to_buy": "number (optional, default: min_stock_qty or 1)"
}
```

**Response 200:**
```json
{
    "found": true,
    "created": false,
    "product": {
        "id": 1,
        "name": "Leche Lala 1L",
        "barcode": "7501234567890",
        "unit_base": "unit",
        "min_stock_qty": 3,
        "performance_index": 85.5
    },
    "inventory": {
        "current_stock": 2,
        "unit": "unit",
        "min_stock": 3,
        "low_stock_alert": true
    },
    "pricing": {
        "last_price": 25.50,
        "vendor": "Superama",
        "captured_on": "2025-12-03",
        "currency": "USD"
    },
    "performance": {
        "index": 85.5,
        "avg_consumption_rate": 0.5
    },
    "alerts": [
        {
            "type": "low_stock",
            "message": "Stock bajo: 2 unit. Mínimo recomendado: 3",
            "severity": "warning"
        }
    ],
    "added_to_list": true,
    "list_item": {...}
}
```

---

## ✅ Checklist de Implementación

### Migraciones y Modelos
- [x] Migración con nuevos campos
- [x] Actualizar fillable en ShoppingList
- [x] Actualizar fillable en FoodProduct
- [x] Actualizar fillable en FoodPrice
- [x] Actualizar fillable en ShoppingListItem
- [x] Ejecutar migraciones exitosamente

### Servicios
- [x] ProductPerformanceService creado
- [x] PriceChangeService creado
- [x] Métodos de cálculo de rendimiento
- [x] Métodos de registro de precios
- [x] Métodos de alertas

### Controladores
- [x] FoodScanController mejorado con buildProductResponse
- [x] ShoppingListController: budget_id requerido en generate
- [x] ShoppingListController: registro de precio real en markItem
- [x] ShoppingListController: actualización de totales
- [ ] Tests unitarios de servicios
- [ ] Tests de integración de API

### UI (Pendiente)
- [ ] Formulario de generación con selector de presupuesto
- [ ] Vista de lista como checklist móvil
- [ ] Input de precio real al marcar item
- [ ] Dashboard de alertas de rendimiento
- [ ] Historial de precios por producto
- [ ] Comparativa de vendors

---

## 🚀 Comandos Útiles

```bash
# Ejecutar migraciones
php artisan migrate

# Calcular rendimiento de todos los productos (consola)
php artisan tinker
>>> $service = app(\App\Services\ProductPerformanceService::class);
>>> $alerts = $service->generatePerformanceAlerts(1); // user_id = 1
>>> dump($alerts);

# Registrar precio manualmente
>>> $priceService = app(\App\Services\PriceChangeService::class);
>>> $product = \App\Models\FoodProduct::find(1);
>>> $result = $priceService->registerPriceChange($product, 28.50, 'Walmart');
>>> dump($result);

# Ver historial de precios
>>> $history = $priceService->getPriceHistory($product, 3); // últimos 3 meses
>>> dump($history);
```

---

## 📞 Soporte y Dudas

Para dudas sobre implementación, contactar al equipo de desarrollo.

**Fecha de implementación:** 2025-12-04
**Versión:** 1.0
**Estado:** ✅ Backend completado, UI pendiente
