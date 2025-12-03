# Modulo Inventario de Alimentos y Gastos del Hogar

## Objetivo y alcance inicial
- Controlar existencias de alimentos con doble clasificacion: ubicacion fisica (alacena, refri, congelador, bodega) y tipo de alimento (lacteos, granos, proteinas, verduras, frutas, condimentos, snacks, limpieza, etc).
- Alertar proactivamente por bajo stock y caducidad proxima.
- Registrar precios y compras, integrando con presupuestos (fondos de alimentos) y calculando precio por unidad de medida.
- Facilitar captura desde movil (escaneo de codigo de barras/QR) y alta rapida de productos y compras.

## Casos de uso clave
- Ver inventario filtrando por ubicacion o tipo y saber existencias por producto.
- Alta rapida de producto (nombre, cantidad, unidad, ubicacion, tipo, fecha de caducidad opcional) desde web o movil.
- Registrar compra con detalle por item: asignar ubicacion y tipo al ingreso, precio unitario y total, y vincular a presupuesto/categoria.
- Ver historial de compras y comparar precios por unidad entre tiendas/fechas.
- Recibir alertas: (1) stock en minimo definido por producto, (2) caducidades proximas, (3) comparativa de gasto vs presupuesto de alimentos.
- Escanear codigo de barras/QR para precargar producto (nombre/brand si existe) y registrar entrada o compra.

## Modelo de datos (tablas nuevas con prefijo `sogar_`)
- `sogar_food_locations`: id, user_id, name, slug, color, sort_order, is_default, timestamps.
- `sogar_food_types`: id, user_id, name, description, color, sort_order, is_active, timestamps.
- `sogar_food_products`: id, user_id, type_id, default_location_id, name, brand, barcode, unit_base (unit|g|kg|ml|l), unit_size (factor a unidad base), shelf_life_days, min_stock_qty, notes, is_active, timestamps. Index en (user_id, barcode), (user_id, name).
- `sogar_food_stock_batches`: id, user_id, product_id, location_id, purchase_item_id nullable, qty_base, qty_remaining_base, unit_base, expires_on nullable, entered_on, opened_at nullable, cost_total, currency, status (ok|consumed|expired|wasted), timestamps.
- `sogar_food_stock_movements`: id, user_id, product_id, batch_id nullable, from_location_id nullable, to_location_id nullable, reason (purchase|consume|transfer|adjust|waste), qty_delta_base (positivo o negativo), occurred_on, note, created_at.
- `sogar_food_purchases`: id, user_id, wallet_id nullable, occurred_on, vendor, receipt_number, total, currency, note, timestamps.
- `sogar_food_purchase_items`: id, purchase_id, product_id, type_id nullable, location_id nullable, qty, unit (unit|g|kg|ml|l), unit_size, unit_price, subtotal, expires_on nullable, budget_id nullable (o category_id para mapear a presupuesto), timestamps.
- `sogar_food_prices`: id, product_id, source (manual|ticket|scan), vendor nullable, currency, price_per_base, captured_on, purchase_item_id nullable, note, timestamps.
- `sogar_food_barcodes`: id, product_id, code (ean/qr), kind (ean13|qr|custom), timestamps. Permite varios codigos por producto.

Notas de calculo:
- `unit_base` define la unidad normalizada; `unit_size` guarda el factor desde la unidad capturada a la base (ej. kg -> 1000g).
- `qty_base` y `qty_remaining_base` permiten calcular existencias y precio por unidad base = cost_total / qty_base.

## Integracion con presupuestos y finanzas
- Cada `purchase_item` puede mapearse a una `category_id` existente y opcionalmente a un `budget_id` del mes. Con esto se puede:
  - Generar automaticamente un `transaction` (gasto) y `wallet_movement` si se selecciona un `wallet_id` y un toggle "impactar finanzas".
  - Mostrar en dashboard el gasto de alimentos vs presupuesto mensual (sumando subtotales vinculados a la categoria de alimentos).
- Configuracion sugerida: flag por usuario "impactar finanzas por defecto" y categoria por defecto "Alimentos".

## Flujo de interfaz (Blade + Tailwind, consistente con panel actual)
- Vista "Inventario" (`/food/inventory`): tarjetas/resumen por ubicacion, tabla filtrable por ubicacion y tipo, chips de estado (stock bajo, caduca en X dias), CTA de entrada rapida.
- Modal/hoja "Entrada rapida": nombre/busqueda de producto, cantidad+unidad, ubicacion, tipo, caducidad, minimo opcional, checkbox "registrar como compra" que abre precio y wallet.
- Vista "Compras" (`/food/purchases`): listado con fecha, proveedor, total, badge de impacto presupuestal, link a detalle; boton "Agregar compra" con formulario maestro-detalle.
- Vista "Productos" (`/food/products`): mantenimiento de productos, minimos, unidad base, barcode, ubicacion y tipo por defecto.
- Panel lateral/alertas: resumen de caducidades proximas y stock bajo; enlaces a ajustar.

## API/Movil y escaneo
- Endpoint POST `/api/food/scan` con payload `{ code, mode: lookup|ingress, qty?, unit?, location_id?, type_id? }`.
  - lookup: devuelve producto si existe y sugerencias para crear nuevo.
  - ingress: crea o incrementa stock (nuevo batch) y opcionalmente registra compra rapida con precio.
- Seguridad: tokens personales con scope `food:write` y rate limit suave.
- Guardar el barcode en `sogar_food_barcodes` si no existia.

## Alertas y jobs
- Job diario que calcula:
  - Caducidades: batches con `expires_on` en <= N dias configurables (default 5).
  - Stock bajo: `qty_remaining_base` < `min_stock_qty`.
  - Gasto vs presupuesto: compara suma de `purchase_items` mapeados a categoria con `budget` del mes.
- Envia notificaciones in-app (y opcional email) y pinta chips en dashboard.

## Backlog propuesto (orden sugerido)
1) Migraciones + modelos + factories de nuevas tablas. Policies basadas en user_id.
2) Servicios: conversor de unidades base, calculadora de alertas, orquestador de compras->finanzas (crea transaction/wallet_movement).
3) UI Inventario: index + modal de entrada rapida (incluye caducidad y minimo).
4) UI Compras: maestro-detalle con items, vinculo a presupuesto/categoria/wallet y precio por unidad.
5) UI Productos: CRUD con barcode y defaults (ubicacion, tipo, unidad base, minimo).
6) API de escaneo y endpoints JSON para autocomplete de productos/ubicaciones/tipos.
7) Alertas y seccion de resumen en dashboard.
8) Mejoras futuras: listas de compras sugeridas (segun minimos y consumo historico), etiquetas de tienda, kit de recetas.

## Preguntas abiertas
- Deseas que toda compra genere automaticamente transaction y wallet_movement, o solo si el usuario marca la opcion?
- Que nivel de unidad de medida requerimos soportar (solo g/ml/unit o tambien onzas/libra)? Se puede empezar con set corto y expandir.
- Se desea soportar multi-usuario colaborativo en mismo hogar (compartir ubicaciones e inventario) o es individual por cuenta actual?
- Para escaneo: hay preferencia por libreria JS especifica o usar lector nativo de dispositivo via input tipo `camera`?
- Caducidad: basta con alerta n dias antes o tambien bloquear salida de stock caducado?
