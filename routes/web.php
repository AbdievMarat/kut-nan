<?php

use App\Http\Controllers\Admin\BusController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\RealizationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Client\RemainderController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [OrderController::class, 'showEnterLicensePlateForm'])->name('orders.enter_license_plate');
Route::post('/process-license-plate', [OrderController::class, 'processLicensePlate'])->name('orders.process_license_plate');
Route::resource('orders', OrderController::class)->only(['create', 'store']);
Route::resource('realizations', RealizationController::class)->only(['create', 'store']);
Route::resource('remainders', RemainderController::class)->only(['create', 'store']);
Route::post('/realizations/add-shop', [RealizationController::class, 'addShop'])->name('realizations.add_shop');

Auth::routes();

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [AdminOrderController::class, 'index'])->name('home');
    Route::resource('buses', BusController::class)->except(['destroy']);
    Route::resource('products', ProductController::class)->except(['destroy']);
    Route::resource('orders', AdminOrderController::class)->only(['index']);
    Route::get('get-realization-shops', [AdminOrderController::class, 'getRealizationShops'])->name('orders.get_realization_shops');
    Route::get('get-remainder-items', [AdminOrderController::class, 'getRemainderItems'])->name('orders.get_remainder_items');
    Route::get('orders_export_to_excel', [AdminOrderController::class, 'exportToExcel'])->name('orders.export_to_excel');
});