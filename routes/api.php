<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// Sales reports and exports (public) - must be defined BEFORE resource routes
Route::get('/sales/reports', [SaleController::class, 'reports'])->name('api.sales.reports');
Route::get('/sales/export', [SaleController::class, 'export'])->name('api.sales.export');
Route::get('/sales/export-test', [SaleController::class, 'export'])->name('api.sales.export.test');

// Chart data API endpoint
Route::get('/sales/chart-data', [DashboardController::class, 'getChartData'])->name('api.sales.chart-data');

// Public API routes (no authentication required)
Route::apiResource('customers', CustomerController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('sales', SaleController::class);

// Test routes (public)
Route::get('/test/customers', [CustomerController::class, 'index']);
Route::get('/test/products', [ProductController::class, 'index']);
Route::get('/test/sales', [SaleController::class, 'index']); 