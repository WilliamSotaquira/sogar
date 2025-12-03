<?php

use App\Http\Controllers\Api\FoodScanController;
use App\Http\Controllers\Api\ShoppingListController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/food/scan', FoodScanController::class)->name('api.food.scan');
    Route::get('/shopping-lists/active', [ShoppingListController::class, 'active'])->name('api.shopping-lists.active');
    Route::post('/shopping-lists/generate', [ShoppingListController::class, 'generate'])->name('api.shopping-lists.generate');
    Route::put('/shopping-lists/{list}/items/{item}', [ShoppingListController::class, 'updateItem'])->name('api.shopping-lists.items.update');
});
