# 🔍 Autocompletado de Productos por Código de Barras

## ✅ Implementación Completada

### 📋 **Funcionalidad**

Al crear un producto desde la lista de compras, ahora puedes **ingresar un código de barras** y el sistema buscará automáticamente la información del producto en:

1. **Tu inventario local** (productos que ya creaste)
2. **OpenFoodFacts** (base de datos mundial de productos)

---

## 🎯 **Cómo Usar**

### Método 1: Desde Lista de Compras

```
1. Click en botón "+ Agregar" (sin producto en la búsqueda)
2. Se abre modal "Crear Producto en Catálogo"
3. En el campo "Código de Barras", ingresa el código
4. Espera 800ms después de escribir
5. 🔍 Sistema busca automáticamente
6. ✅ Campos se autocompletan
7. Revisa y ajusta si es necesario
8. Click "✓ Crear y Vincular"
```

### Método 2: Desde Módulo de Productos

```
1. Ve a /food/products
2. En el formulario de crear producto
3. Ingresa código de barras
4. Espera autocompletado
5. Guarda
```

---

## 🔄 **Flujo Técnico**

```
Usuario escribe código: "7501055363018"
        ↓
Espera 800ms (debounce)
        ↓
GET /food/barcode/7501055363018  (sesión web)
        ↓
[1] ¿Existe en inventario local?
    ✅ SÍ → Retorna datos del producto
    ❌ NO → Continúa a paso [2]
        ↓
[2] ¿Existe en OpenFoodFacts?
    ✅ SÍ → Retorna datos de OpenFoodFacts
    ❌ NO → Error 404
        ↓
Frontend autocompleta campos
```

---

## 📊 **Respuesta de la API**

### Producto en Inventario Local

```json
{
  "found": true,
  "source": "local",
  "data": {
    "id": 5,
    "name": "Leche Lala Entera 1L",
    "brand": "Lala",
    "barcode": "7501055363018",
    "type_id": 2,
    "type_name": "Lácteos",
    "location_id": 3,
    "location_name": "Refrigerador",
    "unit_base": "unit",
    "unit_size": 1,
    "min_stock_qty": 3,
    "shelf_life_days": 7,
    "image_url": "https://..."
  }
}
```

### Producto en OpenFoodFacts

```json
{
  "found": true,
  "source": "openfoodfacts",
  "data": {
    "name": "Coca-Cola Original",
    "brand": "Coca-Cola",
    "barcode": "7501234567890",
    "image_url": "https://images.openfoodfacts.org/...",
    "categories": "beverages, sodas",
    "quantity": "600ml",
    "unit_base": "ml",
    "unit_size": 600,
    "suggested_shelf_life": 180
  }
}
```

### Producto No Encontrado

```json
{
  "found": false,
  "source": null,
  "message": "Producto no encontrado en inventario local ni en OpenFoodFacts"
}
```

---

## 🎨 **Feedback Visual**

### Durante Búsqueda
```
[Código de Barras]: 7501055363018
🔍 Buscando producto...
```

### Encontrado en Inventario
```
[Código de Barras]: 7501055363018
✅ Datos cargados desde tu inventario (Este producto ya existe en tu catálogo)
```

### Encontrado en OpenFoodFacts
```
[Código de Barras]: 7501234567890
✅ Datos cargados desde OpenFoodFacts
```

### No Encontrado
```
[Código de Barras]: 1234567890123
⚠️ Código no encontrado. Completa datos manualmente.
```

### Error de Conexión
```
[Código de Barras]: 7501055363018
❌ Error al buscar. Verifica tu conexión.
```

---

## 🧠 **Lógica de Autocompletado**

### Campos que se Autocompletan

| Campo | Inventario Local | OpenFoodFacts |
|-------|------------------|---------------|
| Nombre | ✅ | ✅ |
| Marca | ✅ | ✅ |
| Tipo | ✅ | ❌ (no aplica) |
| Ubicación | ✅ | ❌ (no aplica) |
| Unidad Base | ✅ | ✅ (inferida) |
| Factor Tamaño | ✅ | ✅ (extraído) |
| Stock Mínimo | ✅ | ❌ |
| Vida Útil | ✅ | ✅ (sugerida) |
| Imagen | ✅ | ✅ |

### Inferencia de Datos desde OpenFoodFacts

**Unidad Base:**
```php
"600ml" → unit_base = "ml", unit_size = 600
"1.5L" → unit_base = "l", unit_size = 1.5
"500g" → unit_base = "g", unit_size = 500
"2kg" → unit_base = "kg", unit_size = 2
```

**Vida Útil Sugerida:**
```php
Categoría "dairy, lácteos" → 7 días
Categoría "meat, carne" → 3 días
Categoría "vegetables" → 5 días
Categoría "bread, pan" → 3 días
Categoría "canned, conserva" → 365 días
Categoría "pasta, rice" → 180 días
Por defecto → 30 días
```

---

## 🔒 **Seguridad**

### Autenticación
- Endpoint protegido con `auth:sanctum`
- Solo usuarios autenticados pueden acceder
- Búsqueda limitada al inventario del usuario

### Validación
```php
'code' => 'required|string|max:255'
```

### Rate Limiting
- API de OpenFoodFacts: timeout 5 segundos
- Debounce en frontend: 800ms
- Auto-cleanup de mensajes: 5 segundos

---

## 🛠️ **Archivos Modificados/Creados**

### Backend

1. **`BarcodeLookupController.php`** (NUEVO)
   - `GET /food/barcode/{code}`
   - Busca en local + OpenFoodFacts
   - Infiere unidades y vida útil

2. **`routes/web.php`**
   - Agregada ruta de barcode lookup

### Frontend

3. **`shopping-list/index.blade.php`**
   - Listener en campo `create-barcode`
   - Autocompletado de campos del modal
   - Feedback visual de búsqueda

> Nota: el endpoint de lookup (`/food/barcode/{code}`) es el recomendado para autocompletar porque no crea productos. El endpoint `/food/scan` está pensado para flujos de escaneo que pueden autocrear.

> Nota técnica: este proyecto no registra `routes/api.php` por defecto (ver `bootstrap/app.php`), así que las rutas `/api/*` no están activas salvo que se habiliten explícitamente.

---

## 📱 **Casos de Uso**

### Caso 1: Producto Ya Existe en Tu Inventario
```
Usuario: Ingresa "7501055363018"
Sistema: "✅ Datos cargados desde tu inventario (ya existe)"
Usuario: Ve que ya tiene el producto
Acción: Cancela creación, agrega producto existente a lista
```

### Caso 2: Producto Nuevo en OpenFoodFacts
```
Usuario: Ingresa "7501234567890"
Sistema: "✅ Datos cargados desde OpenFoodFacts"
Campos autocompletados:
  - Nombre: "Coca-Cola Original"
  - Marca: "Coca-Cola"
  - Unidad: ml (600)
  - Vida útil: 180 días
Usuario: Selecciona tipo "Bebidas" y ubicación "Despensa"
Usuario: Click "Crear y Vincular"
Resultado: Producto creado con datos completos
```

### Caso 3: Código No Encontrado
```
Usuario: Ingresa "9999999999999"
Sistema: "⚠️ Código no encontrado. Completa datos manualmente."
Usuario: Llena todos los campos manualmente
Usuario: Click "Crear y Vincular"
Resultado: Producto creado con datos del usuario
```

---

## 🚀 **Mejoras Futuras Posibles**

1. **Cache de OpenFoodFacts**
   - Guardar respuestas para no consultar múltiples veces
   - TTL: 7 días

2. **Sugerir Productos Similares**
   - Si código no encontrado, buscar por nombre similar
   - "¿Quisiste decir: Leche Lala 1L?"

3. **Edición Masiva**
   - Actualizar múltiples productos con datos de OpenFoodFacts
   - Botón "Enriquecer con OpenFoodFacts"

4. **Imágenes Locales**
   - Descargar y guardar imágenes de OpenFoodFacts
   - Optimizar tamaño

5. **Más Fuentes de Datos**
   - UPC Database
   - Barcode Lookup
   - Amazon Product API

---

## ⚡ **Performance**

### Tiempos de Respuesta

| Fuente | Tiempo Promedio |
|--------|-----------------|
| Inventario local | ~50ms |
| OpenFoodFacts | ~500ms - 2s |
| Timeout | 5s |

### Optimizaciones
- Debounce: 800ms (evita múltiples requests)
- Timeout: 5s (corta requests lentos)
- Cleanup: 5s (limpia mensajes viejos)

---

## 🧪 **Testing**

### Códigos de Barras para Probar

**Productos Mexicanos Comunes:**
```
7501055363018 → Leche Lala Entera 1L
7501000673209 → Coca-Cola 600ml
7501000125807 → Zucaritas Kellogg's 500g
7506205806049 → Sabritas Original 45g
7501030483946 → Bimbo Pan Blanco Grande
```

**Productos Internacionales:**
```
3017620422003 → Nutella 400g (Francia)
5000112548815 → Heinz Ketchup 570g (UK)
8715700110967 → Red Bull 250ml (Austria)
```

### Probar Flujos

✅ Código válido en OpenFoodFacts  
✅ Código ya en tu inventario  
✅ Código inválido/inexistente  
✅ Sin conexión a internet  
✅ Timeout de OpenFoodFacts  
✅ Código parcial (<8 dígitos)  
✅ Código muy largo (>20 dígitos)  

---

## 📞 **Troubleshooting**

### Problema: No autocompleta

**Posibles causas:**
1. Código muy corto (< 8 dígitos)
2. No esperaste 800ms
3. Error de red

**Solución:**
- Verifica código completo
- Espera a ver mensaje de búsqueda
- Revisa consola del navegador (F12)

### Problema: "Error al buscar"

**Causa:** Problema de conexión o API caída

**Solución:**
- Verifica tu conexión
- Intenta nuevamente en 30 segundos
- Completa datos manualmente

### Problema: Datos incorrectos

**Causa:** Datos de OpenFoodFacts desactualizados o incorrectos

**Solución:**
- Edita los campos antes de guardar
- Reporta a OpenFoodFacts (opcional)
- Usa datos manuales

---

**Fecha de implementación:** 2025-12-04  
**Versión:** 1.0  
**Estado:** ✅ Funcional
