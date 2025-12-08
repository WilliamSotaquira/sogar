# Plan de permisos y listas compartidas

## Cambios implementados
- Se agregaron políticas (`app/Policies/FamilyGroupPolicy.php`) y se activó `AuthorizesRequests` en el controlador base para centralizar autorizaciones.
- Los administradores del núcleo ahora pueden gestionar miembros (agregar/editar/quitar) sin requerir rol de administrador del sistema.
- Las listas de compra se pueden compartir con el núcleo familiar: nueva columna `family_group_id` (migración `2025_12_07_180000_add_family_group_to_shopping_lists_table.php`) y control de acceso en `ShoppingListController`.
- Las tarjetas de listas muestran presupuesto, uso y contexto familiar.
- Ruta de actualización de productos corregida a `PUT` (antes era `GET` con nombre de update).
- Backfill para listas existentes: migración `2025_12_07_181500_backfill_family_group_on_existing_shopping_lists.php` asigna `family_group_id` usando el núcleo activo del dueño (si existe).

## Pendientes/operativos
- Ejecutar migraciones para aplicar la columna nueva:
  ```bash
  php artisan migrate
  ```
- Revisar formularios de edición de productos (si se añaden) para que envíen `PUT` a `route('food.products.update')` (la ruta ya fue ajustada).
- Si hay listas existentes que deban compartirse, actualizar `family_group_id` con el núcleo activo correspondiente.
- Revisar formularios de productos para que envíen `PUT` a `food.products.update` (ya se corrigió la ruta).

## Cómo probar
1) Con un usuario miembro de un núcleo (no admin de sistema):
   - Ver `/family` y confirmar que puede añadir/editar/quitar miembros si es admin del núcleo.
   - Activar un núcleo y generar una lista en `/food/shopping-list/all`; verificar que `family_group_id` se guarda y otros miembros pueden verla.
2) Confirmar que un miembro no administrador del núcleo no puede remover la lista completa (403 en DELETE).
3) Probar métricas y barras de presupuesto en tarjetas de listas con una lista que tenga presupuesto asignado.
