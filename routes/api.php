<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\AuthController;

// Authentication routes (no middleware)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Public API routes for testing (no authentication required)
Route::get('/test/customers', [CustomerController::class, 'index']);
Route::get('/test/products', [ProductController::class, 'index']);
Route::get('/test/sales', [SaleController::class, 'index']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('products', ProductController::class);
    Route::get('/sales/reports', [SaleController::class, 'reports'])->name('api.sales.reports');
    Route::get('/sales/export', [SaleController::class, 'export'])->name('api.sales.export');
    Route::get('/sales/export-test', [SaleController::class, 'export'])->name('api.sales.export.test');
    Route::apiResource('sales', SaleController::class);
}); 