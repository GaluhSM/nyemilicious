<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\LogController;

// Public Routes
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/cart', [LandingController::class, 'cart'])->name('cart');
Route::get('/order-form', [LandingController::class, 'orderForm'])->name('order.form');
Route::get('/order-status/{orderCode}', [OrderController::class, 'show'])->name('order.status');
Route::post('/order', [OrderController::class, 'store'])->name('order.store');
Route::patch('/order/{orderCode}/cancel', [OrderController::class, 'cancel'])->name('order.cancel');

// Auth Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
});

// Admin Routes (Protected)
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    Route::get('/sales', [SalesController::class, 'index'])->name('admin.sales');
    
    Route::resource('orders', AdminOrderController::class, [
        'as' => 'admin',
        'only' => ['index', 'store', 'update', 'destroy']
    ]);
    
    Route::resource('accounts', AccountController::class, [
        'as' => 'admin',
        'only' => ['index', 'store', 'update', 'destroy']
    ]);
    
    Route::get('/logs', [LogController::class, 'index'])->name('admin.logs');
});