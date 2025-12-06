# Sistema de Múltiples Listas de Compra - Instrucciones de Implementación

## 📋 Resumen
Sistema mejorado que permite crear y gestionar múltiples listas de compra con diferentes propósitos (Mercado, Aseo, Reparaciones, etc.), agregar productos desde cualquier vista y generar sugeridos automáticos.

## 🎯 Características Implementadas

### 1. Vista de Gestión de Listas (`all.blade.php`)
- ✅ Ver todas las listas de compra en formato de tarjetas
- ✅ Métricas rápidas (total de listas, activas, items, comprados)
- ✅ Crear nuevas listas con nombre personalizado
- ✅ Asignar presupuesto opcional a cada lista
- ✅ Botón para generar sugeridos automáticos
- ✅ Ver progreso de cada lista
- ✅ Eliminar listas

### 2. Componente "Agregar a Lista" (`add-to-shopping-list.blade.php`)
- ✅ Dropdown con todas las listas activas
- ✅ Agregar producto a lista con 1 clic
- ✅ Notificación de confirmación
- ✅ Integrado en vista de productos

## 🔧 Pasos de Implementación Backend

### PASO 1: Actualizar Rutas (`routes/web.php`)

Agregar estas rutas en el grupo de 'food':

```php
// Rutas de Shopping List
Route::prefix('food/shopping-list')->name('food.shopping-list.')->group(function () {
    // Vista de todas las listas
    Route::get('/all', [ShoppingListController::class, 'all'])->name('all');
    
    // Generar sugeridos automáticos para una lista
    Route::post('/{list}/suggest', [ShoppingListController::class, 'generateSuggestions'])->name('suggest');
    
    // Eliminar lista
    Route::delete('/{list}', [ShoppingListController::class, 'destroy'])->name('destroy');
    
    // Rutas existentes...
    Route::get('/', [ShoppingListController::class, 'index'])->name('index');
    Route::post('/generate', [ShoppingListController::class, 'generate'])->name('generate');
    Route::get('/{list}', [ShoppingListController::class, 'show'])->name('show');
    Route::post('/{list}/items/{item}', [ShoppingListController::class, 'updateItem'])->name('items.update');
    
    // Ruta para agregar items desde productos
    Route::post('/items/store', [ShoppingListController::class, 'storeItem'])->name('items.store');
    Route::get('/items/search', [ShoppingListController::class, 'searchProducts'])->name('items.search');
});
```

### PASO 2: Actualizar Controlador (`ShoppingListController.php`)

Agregar estos métodos:

```php
<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\FoodProduct;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ShoppingListController extends Controller
{
    /**
     * Mostrar todas las listas de compra del usuario
     */
    public function all(Request $request): View
    {
        $lists = ShoppingList::where('user_id', $request->user()->id)
            ->with(['items', 'budget.category'])
            ->orderBy('created_at', 'desc')
            ->get();

        $budgets = Budget::where('user_id', $request->user()->id)
            ->with('category')
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->get();

        return view('food.shopping-list.all', [
            'lists' => $lists,
            'budgets' => $budgets,
        ]);
    }

    /**
     * Generar sugeridos automáticos basados en stock bajo
     */
    public function generateSuggestions(Request $request, ShoppingList $list): JsonResponse
    {
        // Verificar que la lista pertenece al usuario
        if ($list->user_id !== $request->user()->id) {
            abort(403);
        }

        // Obtener productos con stock bajo
        $products = FoodProduct::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->get()
            ->filter(function ($product) {
                $currentStock = $product->batches()
                    ->where('status', 'ok')
                    ->sum('qty_remaining_base');
                
                return $product->min_stock_qty > 0 && $currentStock < $product->min_stock_qty;
            });

        $count = 0;
        foreach ($products as $product) {
            // Verificar si ya está en la lista
            $exists = ShoppingListItem::where('shopping_list_id', $list->id)
                ->where('product_id', $product->id)
                ->exists();

            if (!$exists) {
                $currentStock = $product->batches()
                    ->where('status', 'ok')
                    ->sum('qty_remaining_base');

                $qtyToBuy = max(1, $product->min_stock_qty - $currentStock);

                // Obtener precio actual
                $latestPrice = \App\Models\FoodPrice::where('product_id', $product->id)
                    ->orderBy('captured_on', 'desc')
                    ->first();

                ShoppingListItem::create([
                    'shopping_list_id' => $list->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'qty_to_buy_base' => $qtyToBuy,
                    'qty_current_base' => $currentStock,
                    'unit_base' => $product->unit_base,
                    'estimated_price' => $latestPrice ? $latestPrice->price_per_base * $qtyToBuy : 0,
                    'low_stock_alert' => true,
                    'is_checked' => false,
                    'sort_order' => ShoppingListItem::where('shopping_list_id', $list->id)->max('sort_order') + 1,
                ]);

                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "$count productos sugeridos agregados",
        ]);
    }

    /**
     * Eliminar una lista
     */
    public function destroy(Request $request, ShoppingList $list)
    {
        // Verificar que la lista pertenece al usuario
        if ($list->user_id !== $request->user()->id) {
            abort(403);
        }

        $list->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lista eliminada correctamente',
        ]);
    }

    /**
     * Agregar item a una lista (desde productos o manualmente)
     */
    public function storeItem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'list_id' => 'required|exists:shopping_lists,id',
            'product_id' => 'nullable|exists:sogar_food_products,id',
            'name' => 'required|string',
            'qty_to_buy_base' => 'required|numeric|min:0.1',
            'create_product' => 'nullable|boolean',
            // Datos para crear producto si create_product=true
            'brand' => 'nullable|string',
            'barcode' => 'nullable|string',
            'type_id' => 'nullable|exists:sogar_food_types,id',
            'location_id' => 'nullable|exists:sogar_food_locations,id',
            'unit_base' => 'nullable|string',
            'unit_size' => 'nullable|numeric',
            'min_stock_qty' => 'nullable|numeric',
            'shelf_life_days' => 'nullable|integer',
        ]);

        $list = ShoppingList::findOrFail($data['list_id']);

        // Verificar que la lista pertenece al usuario
        if ($list->user_id !== $request->user()->id) {
            abort(403);
        }

        $productId = $data['product_id'] ?? null;

        // Si se solicita crear el producto
        if ($request->input('create_product') && !$productId) {
            $product = FoodProduct::create([
                'user_id' => $request->user()->id,
                'name' => $data['name'],
                'brand' => $data['brand'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'type_id' => $data['type_id'] ?? null,
                'default_location_id' => $data['location_id'] ?? null,
                'unit_base' => $data['unit_base'] ?? 'unit',
                'unit_size' => $data['unit_size'] ?? 1,
                'min_stock_qty' => $data['min_stock_qty'] ?? 1,
                'shelf_life_days' => $data['shelf_life_days'] ?? null,
                'is_active' => true,
            ]);
            $productId = $product->id;
        }

        // Obtener datos del producto si existe
        $product = $productId ? FoodProduct::find($productId) : null;
        $currentStock = 0;
        $estimatedPrice = 0;

        if ($product) {
            $currentStock = $product->batches()->where('status', 'ok')->sum('qty_remaining_base');
            $latestPrice = \App\Models\FoodPrice::where('product_id', $product->id)
                ->orderBy('captured_on', 'desc')
                ->first();
            $estimatedPrice = $latestPrice ? $latestPrice->price_per_base * $data['qty_to_buy_base'] : 0;
        }

        // Crear el item
        ShoppingListItem::create([
            'shopping_list_id' => $list->id,
            'product_id' => $productId,
            'name' => $data['name'],
            'qty_to_buy_base' => $data['qty_to_buy_base'],
            'qty_current_base' => $currentStock,
            'unit_base' => $product?->unit_base ?? 'unit',
            'estimated_price' => $estimatedPrice,
            'is_checked' => false,
            'sort_order' => ShoppingListItem::where('shopping_list_id', $list->id)->max('sort_order') + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Producto agregado a la lista',
        ]);
    }

    /**
     * Buscar productos para agregar a la lista
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $term = $request->input('q', '');
        
        if (strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $products = FoodProduct::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%$term%")
                    ->orWhere('brand', 'like', "%$term%");
            })
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $stock = $product->batches()->where('status', 'ok')->sum('qty_remaining_base');
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'stock' => $stock,
                    'unit_base' => $product->unit_base,
                ];
            });

        return response()->json(['data' => $products]);
    }
}
```

### PASO 3: Actualizar Migración (si es necesario)

Verificar que la tabla `shopping_lists` tenga estos campos:

```php
Schema::table('shopping_lists', function (Blueprint $table) {
    $table->string('list_type')->default('general')->after('name'); // food, cleaning, maintenance, other
    // Campos existentes: name, user_id, budget_id, status, etc.
});
```

Si falta, crear migración:
```bash
php artisan make:migration add_list_type_to_shopping_lists_table
```

### PASO 4: Actualizar Navegación

En tu layout principal o menú, agregar enlace a la vista de todas las listas:

```blade
<a href="{{ route('food.shopping-list.all') }}">
    📋 Mis Listas
</a>
```

## ✅ Verificación

1. Navegar a `/food/shopping-list/all`
2. Crear una nueva lista de compra
3. Ir a productos y usar el botón "➕ Lista"
4. Ver el producto agregado en la lista
5. Probar generar sugeridos automáticos

## 📝 Notas Importantes

- El componente `add-to-shopping-list` requiere Alpine.js para funcionar (dropdown)
- Si no tienes Alpine.js, agregar en el layout:
  ```html
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  ```

## 🎨 Mejoras Futuras Sugeridas

1. Compartir listas con otros usuarios del hogar
2. Templates de listas (guardar listas como plantillas)
3. Organizar items por categorías dentro de la lista
4. Modo offline con sincronización
5. Escaneo de códigos de barras desde la lista
6. Notificaciones cuando hay productos con stock bajo

---

**Última actualización:** 2025-12-06
**Autor:** Sistema de Gestión de Inventario Doméstico
