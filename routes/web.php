<?php

use App\Http\Controllers\Admin\BusController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [OrderController::class, 'showEnterLicensePlateForm'])->name('orders.enter_license_plate');
Route::post('/process-license-plate', [OrderController::class, 'processLicensePlate'])->name('orders.process_license_plate');
Route::resource('orders', OrderController::class)->only(['create', 'store']);

Auth::routes();

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [AdminOrderController::class, 'index'])->name('home');
    Route::resource('buses', BusController::class)->except(['destroy']);
    Route::resource('orders', AdminOrderController::class)->only(['index']);
    Route::get('orders_export_to_excel', [AdminOrderController::class, 'exportToExcel'])->name('orders.export_to_excel');
});