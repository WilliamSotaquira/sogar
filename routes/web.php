<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\FamilyGroupController;
use App\Http\Controllers\GoogleIntegrationController;
use App\Http\Controllers\Food\InventoryController;
use App\Http\Controllers\Food\ProductController as FoodProductController;
use App\Http\Controllers\Food\PurchaseController as FoodPurchaseController;
use App\Http\Controllers\Food\ShoppingListController as FoodShoppingListController;
use App\Http\Controllers\RecurrenceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::get('budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::delete('budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('recurrences', [RecurrenceController::class, 'index'])->name('recurrences.index');
    Route::post('recurrences', [RecurrenceController::class, 'store'])->name('recurrences.store');
    Route::delete('recurrences/{recurrence}', [RecurrenceController::class, 'destroy'])->name('recurrences.destroy');
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::post('wallets', [WalletController::class, 'store'])->name('wallets.store');
    Route::put('wallets/{wallet}', [WalletController::class, 'update'])->name('wallets.update');
    Route::delete('wallets/{wallet}', [WalletController::class, 'destroy'])->name('wallets.destroy');
    Route::post('wallets/{wallet}/movements', [WalletController::class, 'storeMovement'])->name('wallets.movements.store');
    Route::get('integrations/google', [GoogleIntegrationController::class, 'redirect'])->name('integrations.google.redirect');
    Route::get('integrations/google/callback', [GoogleIntegrationController::class, 'callback'])->name('integrations.google.callback');
    Route::delete('integrations/google', [GoogleIntegrationController::class, 'disconnect'])->name('integrations.google.disconnect');

    // Family Groups (Núcleos Familiares)
    Route::get('family', [FamilyGroupController::class, 'index'])->name('family.index');
    Route::get('family/create', [FamilyGroupController::class, 'create'])->name('family.create');
    Route::post('family', [FamilyGroupController::class, 'store'])->name('family.store');
    Route::get('family/{familyGroup}', [FamilyGroupController::class, 'show'])->name('family.show');
    Route::get('family/{familyGroup}/edit', [FamilyGroupController::class, 'edit'])->name('family.edit');
    Route::put('family/{familyGroup}', [FamilyGroupController::class, 'update'])->name('family.update');
    Route::post('family/{familyGroup}/set-active', [FamilyGroupController::class, 'setActive'])->name('family.set-active');
    Route::post('family/{familyGroup}/members', [FamilyGroupController::class, 'addMember'])->name('family.members.add');
    Route::put('family/{familyGroup}/members/{member}', [FamilyGroupController::class, 'updateMember'])->name('family.members.update');
    Route::delete('family/{familyGroup}/members/{memberId}', [FamilyGroupController::class, 'removeMember'])->name('family.members.remove');


    // Food module
    Route::get('food/inventory', [InventoryController::class, 'index'])->name('food.inventory.index');
    Route::get('food/products', [FoodProductController::class, 'index'])->name('food.products.index');
    Route::post('food/products', [FoodProductController::class, 'store'])->name('food.products.store');
    Route::get('food/products/{product}', [FoodProductController::class, 'update'])->name('food.products.update');
    Route::delete('food/products/{product}', [FoodProductController::class, 'destroy'])->name('food.products.destroy');

    // Tipos de alimentos
    Route::get('food/types', [\App\Http\Controllers\Food\TypeController::class, 'index'])->name('food.types.index');
    Route::post('food/types', [\App\Http\Controllers\Food\TypeController::class, 'store'])->name('food.types.store');
    Route::put('food/types/{type}', [\App\Http\Controllers\Food\TypeController::class, 'update'])->name('food.types.update');
    Route::delete('food/types/{type}', [\App\Http\Controllers\Food\TypeController::class, 'destroy'])->name('food.types.destroy');

    // Ubicaciones de alimentos
    Route::get('food/locations', [\App\Http\Controllers\Food\LocationController::class, 'index'])->name('food.locations.index');
    Route::post('food/locations', [\App\Http\Controllers\Food\LocationController::class, 'store'])->name('food.locations.store');
    Route::put('food/locations/{location}', [\App\Http\Controllers\Food\LocationController::class, 'update'])->name('food.locations.update');
    Route::delete('food/locations/{location}', [\App\Http\Controllers\Food\LocationController::class, 'destroy'])->name('food.locations.destroy');

    // Gestión de precios
    Route::get('food/products/{product}/prices', [\App\Http\Controllers\Food\PriceController::class, 'show'])->name('food.prices.show');
    Route::post('food/products/{product}/prices', [\App\Http\Controllers\Food\PriceController::class, 'store'])->name('food.prices.store');
    Route::get('food/products/{product}/prices/chart', [\App\Http\Controllers\Food\PriceController::class, 'chartData'])->name('food.prices.chart');
    Route::get('food/products/{product}/prices/forecast', [\App\Http\Controllers\Food\PriceController::class, 'forecast'])->name('food.prices.forecast');

    Route::get('food/purchases', [FoodPurchaseController::class, 'index'])->name('food.purchases.index');
    Route::post('food/purchases', [FoodPurchaseController::class, 'store'])->name('food.purchases.store');
    
    // Shopping Lists - Vista de todas las listas
    Route::get('food/shopping-list/all', [FoodShoppingListController::class, 'all'])->name('food.shopping-list.all');
    Route::post('food/shopping-list/{list}/suggest', [FoodShoppingListController::class, 'generateSuggestions'])->name('food.shopping-list.suggest');
    
    // Shopping Lists - Rutas existentes
    Route::get('food/shopping-list', [FoodShoppingListController::class, 'index'])->name('food.shopping-list.index');
    Route::post('food/shopping-list/generate', [FoodShoppingListController::class, 'generate'])->name('food.shopping-list.generate');
    Route::put('food/shopping-list/{list}', [FoodShoppingListController::class, 'update'])->name('food.shopping-list.update');
    Route::post('food/shopping-list/sync', [FoodShoppingListController::class, 'sync'])->name('food.shopping-list.sync');
    Route::post('food/shopping-list/{list}/items/{itemId}', [FoodShoppingListController::class, 'markItem'])
        ->whereNumber('itemId')
        ->name('food.shopping-list.items.mark');
    Route::post('food/shopping-list/items', [FoodShoppingListController::class, 'storeItem'])->name('food.shopping-list.items.store');
    Route::delete('food/shopping-list/{list}/items/{item}', [FoodShoppingListController::class, 'destroyItem'])->name('food.shopping-list.items.destroy');
    Route::post('food/shopping-list/{list}/items/bulk', [FoodShoppingListController::class, 'bulkAction'])->name('food.shopping-list.items.bulk');
    Route::get('food/shopping-list/search-products', [FoodShoppingListController::class, 'searchProducts'])->name('food.shopping-list.items.search');
    Route::delete('food/shopping-list/{list}', [FoodShoppingListController::class, 'destroy'])->name('food.shopping-list.destroy');
    Route::get('food/shopping-list/{list}', [FoodShoppingListController::class, 'show'])->name('food.shopping-list.show');

    // Lookup de código de barras desde sesión web
    Route::post('food/scan', \App\Http\Controllers\Api\FoodScanController::class)->name('food.scan');
    Route::get('food/barcode/{code}', [\App\Http\Controllers\Api\BarcodeLookupController::class, 'lookup'])->name('food.barcode.lookup');
});
