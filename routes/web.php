<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerWebController;
use App\Http\Controllers\ProductWebController;
use App\Http\Controllers\SaleWebController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/dashboard', [DashboardController::class, 'index']);

Route::get('/sales/reports', [DashboardController::class, 'reports'])->name('sales.reports');

Route::resource('customers', CustomerWebController::class);
Route::resource('products', ProductWebController::class);
Route::resource('sales', SaleWebController::class);
