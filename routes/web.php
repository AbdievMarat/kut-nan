<?php

use App\Http\Controllers\Admin\BusController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\IngredientMovementController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Client\MarkdownController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\RealizationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Client\RemainderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OrderController::class, 'showEnterLicensePlateForm'])->name('orders.enter_license_plate');
Route::post('/process-license-plate', [OrderController::class, 'processLicensePlate'])->name('orders.process_license_plate');
Route::resource('orders', OrderController::class)->only(['create', 'store']);
Route::resource('realizations', RealizationController::class)->only(['create', 'store']);
Route::resource('remainders', RemainderController::class)->only(['create', 'store']);
Route::resource('markdowns', MarkdownController::class)->only(['create', 'store']);
Route::post('/realizations/add-shop', [RealizationController::class, 'addShop'])->name('realizations.add_shop');

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminOrderController::class, 'index'])->name('home');
    Route::resource('buses', BusController::class)->except(['destroy']);
    Route::get('/buses/{bus}/product_prices/edit', [BusController::class, 'editProductPrices'])->name('buses.product_prices_edit');
    Route::put('/buses/{bus}/product_prices', [BusController::class, 'updateProductPrices'])->name('buses.product_prices_update');
    Route::resource('products', ProductController::class)->except(['show', 'destroy']);
    Route::get('/products/{product}/ingredients', [ProductController::class, 'ingredients'])->name('products.ingredients');
    Route::post('/products/{product}/ingredients', [ProductController::class, 'storeIngredients'])->name('products.ingredients.store');
    Route::resource('ingredients', IngredientController::class)->except(['show', 'destroy']);
});

Route::middleware(['auth', 'role:admin,manager'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('orders', AdminOrderController::class)->only(['index']);
    Route::get('get-markdown-items', [AdminOrderController::class, 'getMarkdownItems'])->name('orders.get_markdown_items');
    Route::get('get-realization-shops', [AdminOrderController::class, 'getRealizationShops'])->name('orders.get_realization_shops');
    Route::get('get-remainder-items', [AdminOrderController::class, 'getRemainderItems'])->name('orders.get_remainder_items');
    Route::get('orders_export_to_excel', [AdminOrderController::class, 'exportToExcel'])->name('orders.export_to_excel');

    Route::get('/', [IngredientMovementController::class, 'index']);
    Route::get('ingredient-movements/edit/{date?}', [IngredientMovementController::class, 'edit'])
        ->name('ingredient-movements.edit-by-date')
        ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}');
    Route::get('ingredient-movements/show/{date}', [IngredientMovementController::class, 'show'])
        ->name('ingredient-movements.show-by-date')
        ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}');
    Route::get('ingredient-movements/show/{date}/export', [IngredientMovementController::class, 'exportShowToExcel'])
        ->name('ingredient-movements.show-export')
        ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}');
    Route::get('ingredient-movements/cost-details', [IngredientMovementController::class, 'getCostDetails'])
        ->name('ingredient-movements.cost-details');
    Route::put('ingredient-movements/{date}', [IngredientMovementController::class, 'update'])
        ->name('ingredient-movements.update-by-date')
        ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}');
    Route::get('ingredient-movements-export', [IngredientMovementController::class, 'exportToExcel'])->name('ingredient-movements.export_to_excel');
    Route::resource('ingredient-movements', IngredientMovementController::class)->only('index', 'create', 'store');
});
