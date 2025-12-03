<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BudgetController;
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

    // Food module
    Route::get('', [InventoryController::class, 'index'])->name('food.inventory.index');
    Route::get('food/products', [FoodProductController::class, 'index'])->name('food.products.index');
    Route::post('food/products', [FoodProductController::class, 'store'])->name('food.products.store');
    Route::put('food/products/{product}', [FoodProductController::class, 'update'])->name('food.products.update');
    Route::delete('food/products/{product}', [FoodProductController::class, 'destroy'])->name('food.products.destroy');
    Route::get('food/purchases', [FoodPurchaseController::class, 'index'])->name('food.purchases.index');
    Route::post('food/purchases', [FoodPurchaseController::class, 'store'])->name('food.purchases.store');
    Route::get('food/shopping-list', [FoodShoppingListController::class, 'index'])->name('food.shopping-list.index');
    Route::post('food/shopping-list/generate', [FoodShoppingListController::class, 'generate'])->name('food.shopping-list.generate');
    Route::post('food/shopping-list/sync', [FoodShoppingListController::class, 'sync'])->name('food.shopping-list.sync');
    Route::post('food/shopping-list/{list}/items/{itemId}', [FoodShoppingListController::class, 'markItem'])->name('food.shopping-list.items.mark');
});
