<?php

use App\Http\Controllers\BranchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseOrderDetailController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SalesOrderDetailController;
use App\Http\Controllers\TransferStockController;
// use App\Http\Controllers\RequestController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\MoneyFlowController;
use Pest\ArchPresets\Custom;

// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/me', [UserController::class, 'me']);
    Route::get('/user', [UserController::class, 'index']);

    // Hanya owner master
    Route::middleware('check.role:owner master')->get('/o-master', function () {
        return response()->json(['message' => 'Dashboard Owner Master']);
    });

    // Hanya owner cabang
    Route::middleware('check.role:owner cabang')->get('/o-cabang', function () {
        return response()->json(['message' => 'Dashboard Owner Cabang']);
    });

    // Hanya kasir
    Route::middleware('check.role:kasir')->get('/kasir', function () {
        return response()->json(['message' => 'Dashboard Kasir']);
    });
});

// Owner master create product
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('product', ProductController::class);
    Route::apiResource('product-types', ProductTypeController::class);
    Route::apiResource('unit', UnitController::class);
    Route::get('/unit/low-stock', [UnitController::class, 'getLowStock']);
});

// Purchase
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('purchase-order', PurchaseOrderController::class);
    Route::post('/purchase-order/complete', [PurchaseOrderController::class, 'complete']);
    Route::apiResource('purchase-order-detail', PurchaseOrderDetailController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('sales-order', SalesOrderController::class);
    Route::post('/sales-order/complete', [SalesOrderController::class, 'complete']);
    Route::apiResource('sales-order-detail', SalesOrderDetailController::class);
    // routes/api.php
    Route::get('/sales-order/latest', [SalesOrderController::class, 'latest']);

});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('transfer-stock', TransferStockController::class);
    // Route::apiResource('request-stock', RequestController::class);
    // Route::put('/request-stock/{id}', [RequestController::class, 'update']);
});

// Customer & Delivery
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('customer', CustomerController::class);
    Route::apiResource('delivery', DeliveryController::class);
});

// Customer & Delivery
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('mny', MoneyFlowController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('branch', BranchController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('safety-stock', [UnitController::class, 'getSafetyStock']);
    Route::get('unit/batch-predictions', [UnitController::class, 'getBatchPredictions']);
});
