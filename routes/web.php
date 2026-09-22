<?php

use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\BootstrapController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'spa'])->name('spa');

Route::prefix('api')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])->name('api.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

        Route::get('/bootstrap', BootstrapController::class);

        Route::post('/settings', [SettingsController::class, 'update']);
        Route::delete('/account', [SettingsController::class, 'destroy']);
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read']);

        Route::post('/ai/chat', [AiChatController::class, 'chat']);

        Route::middleware('role:admin')->group(function () {
            Route::post('/users', [UserController::class, 'store']);
            Route::delete('/users/{user}', [UserController::class, 'destroy']);
            Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
        });

        Route::middleware('role:admin,supervisor')->group(function () {
            Route::get('/reports', [ReportController::class, 'index']);
            Route::get('/reports/export', [ReportController::class, 'export']);
        });
        Route::middleware('role:admin,petugas')->group(function () {
            Route::post('/transactions/in', [TransactionController::class, 'storeIn']);
            Route::post('/transactions/out', [TransactionController::class, 'storeOut']);
        });
        Route::middleware('role:admin,supervisor')->group(function () {
            Route::post('/products', [ProductController::class, 'store']);
            Route::put('/products/{product}', [ProductController::class, 'update']);
            Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        });
    });
});
