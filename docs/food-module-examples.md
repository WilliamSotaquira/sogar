# Ejemplos de Uso - Módulo de Alimentos Mejorado

## 🎯 Escenarios de Uso Práctico

### Escenario 1: Usuario crea presupuesto y genera lista

**Paso 1: Crear presupuesto mensual**
```php
// Via web o API
POST /budgets
{
    "category_id": 5,              // Categoría "Alimentación"
    "amount": 5000.00,
    "month": 12,
    "year": 2025,
    "is_flexible": false
}
```

**Paso 2: Generar lista de compras**
```php
POST /food/shopping-list/generate
{
    "budget_id": 1,                // ID del presupuesto creado
    "name": "Compra semanal Dic 1",
    "horizon_days": 7,
    "people_count": 4,
    "expected_purchase_on": "2025-12-07"
}
```

**Resultado:**
- Lista creada con productos bajo stock mínimo
- Vinculada al presupuesto de $5,000
- Estado: activa (ready para comprar)

---

### Escenario 2: Compra en supermercado con escaneo

**Usuario en tienda física:**

**Item 1: Escanea leche (producto existente)**
```javascript
POST /api/food/scan
{
    "code": "7501055363018",
    "add_to_list": true,
    "qty_to_buy": 2
}

// Respuesta:
{
    "found": true,
    "product": {
        "name": "Leche Lala Entera 1L",
        "min_stock_qty": 3
    },
    "inventory": {
        "current_stock": 1,
        "low_stock_alert": true
    },
    "pricing": {
        "last_price": 25.50,
        "vendor": "Superama",
        "captured_on": "2025-11-28"
    },
    "alerts": [
        {
            "type": "low_stock",
            "message": "Stock bajo: 1 unit. Mínimo: 3",
            "severity": "warning"
        }
    ],
    "added_to_list": true
}
```

**Interpretación:**
- ⚠️ Stock crítico (1/3)
- 💰 Último precio: $25.50 hace 6 días
- ✅ Agregado automáticamente a lista (2 unidades)

**Item 2: Escanea producto nuevo (no existe en BD)**
```javascript
POST /api/food/scan
{
    "code": "7501000123456",
    "name": "Cereal Kelloggs Zucaritas 500g"
}

// Respuesta:
{
    "found": false,
    "created": true,
    "product": {
        "id": 42,
        "name": "Cereal Kelloggs Zucaritas 500g",
        "barcode": "7501000123456"
    },
    "inventory": {
        "current_stock": 0,
        "low_stock_alert": false
    },
    "alerts": []
}
```

**Interpretación:**
- ℹ️ Producto creado automáticamente
- 📦 Stock: 0 (aún no comprado)
- Usuario decide si agregarlo a lista

---

### Escenario 3: Marcar items durante compra

**Usuario va marcando en su lista:**

**Item 1: Leche (con precio)**
```php
POST /food/shopping-list/1/items/3
{
    "is_checked": true,
    "actual_price": 54.00,         // $27 c/u × 2
    "vendor_name": "Soriana"
}
```

**¿Qué pasa internamente?**
1. ✅ Item marcado como comprado
2. 📅 Timestamp: `checked_at = 2025-12-04 14:32:15`
3. 💰 Guarda precio real: $54.00
4. 🏪 Vendor: "Soriana"
5. 📊 **Calcula cambio de precio:**
   - Anterior: $25.50 (Superama)
   - Nuevo: $27.00 (Soriana)
   - Cambio: +5.9%
   - Alerta: NO (< 10%)
6. 📦 **Crea batch en inventario:**
   - 2 unidades
   - Costo: $54.00
   - Caducidad: +7 días (shelf_life_days del producto)
7. 💵 **Actualiza total de lista:**
   - actual_total += $54.00

**Item 2: Pan (sin precio, usa estimado)**
```php
POST /food/shopping-list/1/items/5
{
    "is_checked": true
}
```

**¿Qué pasa?**
1. ✅ Marcado como comprado
2. 💰 Usa `estimated_price` para inventario y total
3. 📦 Crea batch con precio estimado
4. ⚠️ NO registra en historial de precios (no hay precio real)

---

### Escenario 4: Finalizar compra y revisar gastos

**Consultar lista completa:**
```php
GET /food/shopping-list/1

{
    "id": 1,
    "name": "Compra semanal Dic 1",
    "budget_id": 1,
    "estimated_budget": 450.00,
    "actual_total": 487.50,
    "status": "active",
    "items": [
        {
            "name": "Leche Lala 1L",
            "qty_to_buy_base": 2,
            "estimated_price": 51.00,
            "actual_price": 54.00,
            "is_checked": true,
            "vendor_name": "Soriana"
        },
        {
            "name": "Pan Integral",
            "qty_to_buy_base": 3,
            "estimated_price": 35.00,
            "actual_price": null,
            "is_checked": true
        },
        // ... más items
    ]
}
```

**Análisis:**
- 📊 Estimado: $450.00
- 💰 Real: $487.50
- 📈 Diferencia: +$37.50 (+8.3%)
- 💳 Presupuesto usado: $487.50 / $5,000 (9.75%)

**Cerrar lista (opcional):**
```php
PUT /food/shopping-list/1
{
    "status": "closed"
}
```

---

### Escenario 5: Ver rendimiento de productos

**Calcular índice automáticamente:**
```php
use App\Services\ProductPerformanceService;

$service = app(ProductPerformanceService::class);
$product = FoodProduct::find(5); // Leche

$index = $service->calculatePerformanceIndex($product);
// Retorna: 85.5

// El producto ahora tiene:
$product->performance_index;        // 85.5
$product->avg_consumption_rate;     // 0.5 (unidades/día)
$product->last_performance_calc;    // 2025-12-04
```

**Factores calculados para este índice:**

| Factor | Valor | Puntos |
|--------|-------|--------|
| Base | - | 50 |
| Duración (7 días) | Corta | +5 |
| Desperdicio | 3% | +10 |
| Rotación | Rápida | +20 |
| Precio estable | Variación 8% | +8 |
| Disponibilidad | Siempre hay | +5 |
| **TOTAL** | - | **98** ✅ |

**Interpretación:**
- 🌟 **Excelente producto** (>80)
- ✅ Bajo desperdicio
- 🔄 Alta rotación
- 💰 Precio predecible
- 📦 Siempre disponible

**Recomendación:** Mantener en lista de compras regular

---

### Escenario 6: Alertas de precio

**Mes 1: Precio normal**
```php
// Nov 15: Compra en Superama
$priceService->registerPriceChange(
    $product,      // Arroz
    42.00,
    'Superama'
);
// Sin alerta (primer precio)
```

**Mes 2: Subida moderada**
```php
// Dic 1: Compra en Walmart
$result = $priceService->registerPriceChange(
    $product,
    45.50,
    'Walmart'
);

// $result:
[
    'price_change' => 3.50,
    'price_change_percent' => 8.3,
    'is_alert' => false,           // <10%, no alerta
    'alert_message' => null
]
```

**Mes 3: Subida significativa** 🚨
```php
// Dic 15: Compra en Soriana
$result = $priceService->registerPriceChange(
    $product,
    52.00,
    'Soriana'
);

// $result:
[
    'price_change' => 6.50,
    'price_change_percent' => 14.3,
    'is_alert' => true,            // >10%, ¡ALERTA!
    'alert_message' => '⚠️ El precio de Arroz ha subido 14.3% en Soriana (de $45.50 a $52.00)'
]
```

**Mostrar en dashboard:**
```php
$alerts = $priceService->getPriceAlerts($userId, 30);

foreach ($alerts as $alert) {
    echo "{$alert['product']}: {$alert['change_percent']}% en {$alert['vendor']}";
    // "Arroz: +14.3% en Soriana"
}
```

---

### Escenario 7: Comparar precios entre vendors

**Historial de compras:**
```php
$comparison = $priceService->comparePricesByVendor($product);

// Resultado:
[
    [
        'vendor' => 'Walmart',
        'avg_price' => 44.25,
        'last_price' => 45.50,
        'last_date' => '2025-12-01',
        'price_count' => 4
    ],
    [
        'vendor' => 'Superama',
        'avg_price' => 46.50,
        'last_price' => 42.00,
        'last_date' => '2025-11-15',
        'price_count' => 2
    ],
    [
        'vendor' => 'Soriana',
        'avg_price' => 49.00,
        'last_price' => 52.00,
        'last_date' => '2025-12-15',
        'price_count' => 3
    ]
]
```

**Recomendación:**
🏆 **Walmart** tiene mejor precio promedio: $44.25  
⚠️ **Soriana** es el más caro: $49.00

---

### Escenario 8: Productos con bajo rendimiento

**Caso: Lechuga (desperdicio alto)**

```php
$service = app(ProductPerformanceService::class);
$lechuga = FoodProduct::where('name', 'Lechuga Romana')->first();

$index = $service->calculatePerformanceIndex($lechuga);
// Retorna: 28.5 ⚠️
```

**Factores:**
| Factor | Valor | Puntos |
|--------|-------|--------|
| Base | - | 50 |
| Duración | 7 días | +5 |
| **Desperdicio** | **35%** | **-30** ⚠️ |
| Rotación | Lenta | -10 |
| Precio | Volátil (25%) | -10 |
| Desabasto | 15% días | -5 |
| **TOTAL** | - | **0** ❌ |

**Alertas generadas:**
```php
$alerts = $service->generatePerformanceAlerts($userId);

$alerts['low_performance']:
[
    'product' => FoodProduct(Lechuga Romana),
    'score' => 28.5,
    'message' => "El producto 'Lechuga Romana' tiene bajo rendimiento. Considera reducir compras o buscar alternativas."
]
```

**Recomendaciones automáticas:**
1. 🔻 Reducir cantidad mínima de stock
2. 🔄 Comprar más seguido en menor cantidad
3. 🥗 Buscar alternativas (lechuga hidropónica de mayor duración)
4. ❄️ Mejorar almacenamiento

---

## 🎓 Mejores Prácticas

### 1. Configuración Inicial de Productos

**Al crear producto nuevo:**
```php
FoodProduct::create([
    'name' => 'Leche Lala 1L',
    'barcode' => '7501055363018',
    'unit_base' => 'unit',
    'unit_size' => 1,
    'shelf_life_days' => 7,        // ✅ IMPORTANTE
    'min_stock_qty' => 3,          // ✅ IMPORTANTE
    'presentation_qty' => 1,
    'image_url' => 'https://...',
    'default_location_id' => 2,    // Refrigerador
    'type_id' => 1,                // Lácteos
]);
```

**Campos críticos:**
- `shelf_life_days`: Para cálculo de caducidad y rendimiento
- `min_stock_qty`: Para generar listas automáticamente
- `default_location_id`: Para ingreso automático al inventario

### 2. Flujo Recomendado de Compras

1. **Antes de salir:**
   - Generar lista con presupuesto
   - Revisar productos sugeridos
   - Ajustar cantidades

2. **En tienda:**
   - Escanear para verificar precios históricos
   - Ver alertas de stock/rendimiento
   - Tomar decisiones informadas

3. **Al comprar:**
   - Marcar items
   - **SIEMPRE** ingresar precio real
   - **SIEMPRE** ingresar vendor

4. **Al regresar:**
   - Cerrar lista
   - Revisar gasto vs presupuesto
   - Ajustar para siguiente compra

### 3. Mantenimiento de Datos

**Calcular rendimiento mensualmente:**
```php
// Crear job programado
php artisan make:job CalculateProductPerformance

// En el job:
$service = app(ProductPerformanceService::class);
$users = User::all();

foreach ($users as $user) {
    $products = FoodProduct::where('user_id', $user->id)
        ->where('is_active', true)
        ->get();
    
    foreach ($products as $product) {
        $service->calculatePerformanceIndex($product);
    }
}
```

**Limpiar listas antiguas:**
```php
// Cerrar listas de hace más de 30 días
ShoppingList::where('status', 'active')
    ->where('generated_at', '<', now()->subDays(30))
    ->update(['status' => 'closed']);

// Eliminar listas cerradas de hace más de 6 meses
ShoppingList::where('status', 'closed')
    ->where('updated_at', '<', now()->subMonths(6))
    ->delete();
```

---

## 🐛 Troubleshooting

### Problema: Lista no se genera

**Causa:** Falta budget_id
```php
// ❌ Error
POST /food/shopping-list/generate
{
    "name": "Mi lista"
}

// ✅ Correcto
POST /food/shopping-list/generate
{
    "name": "Mi lista",
    "budget_id": 5
}
```

### Problema: Escaneo no muestra alertas

**Causa:** Producto sin min_stock_qty configurado
```php
// Solución: Actualizar producto
$product->update([
    'min_stock_qty' => 3,
    'shelf_life_days' => 7
]);
```

### Problema: Índice de rendimiento siempre 50

**Causa:** Sin datos históricos suficientes
```php
// Necesita:
// - Al menos 2 compras (para precios)
// - Al menos 1 batch consumido (para rotación)
// - Mínimo 30 días de datos

// Solución: Esperar a tener más histórico
// o ajustar algoritmo en ProductPerformanceService
```

---

## 📊 Reportes Útiles

### Productos más rentables
```php
$topPerformers = FoodProduct::where('user_id', $userId)
    ->where('performance_index', '>=', 80)
    ->orderByDesc('performance_index')
    ->limit(10)
    ->get();

echo "Top 10 productos con mejor rendimiento:\n";
foreach ($topPerformers as $p) {
    echo "{$p->name}: {$p->performance_index}/100\n";
}
```

### Productos problemáticos
```php
$lowPerformers = FoodProduct::where('user_id', $userId)
    ->where('performance_index', '<=', 40)
    ->orderBy('performance_index')
    ->get();

echo "Productos con bajo rendimiento:\n";
foreach ($lowPerformers as $p) {
    echo "{$p->name}: {$p->performance_index}/100\n";
}
```

### Vendors más económicos
```php
$vendors = DB::table('sogar_food_prices')
    ->select('vendor', DB::raw('AVG(price_per_base) as avg_price'))
    ->whereNotNull('vendor')
    ->groupBy('vendor')
    ->orderBy('avg_price')
    ->get();

echo "Ranking de vendors por precio promedio:\n";
foreach ($vendors as $v) {
    echo "{$v->vendor}: \${$v->avg_price}\n";
}
```

---

**Última actualización:** 2025-12-04  
**Versión:** 1.0
