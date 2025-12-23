<?php
// routes/admin.php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\GoogleAuthController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\LinkedWithGoogleMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PaymentGatewayController;


Route::prefix('')->name('admin.')->group(function(){

  Route::middleware(['auth', IsAdmin::class])->group(function(){

    Route::middleware(LinkedWithGoogleMiddleware::class)->group(function(){
        Route::controller(DashboardController::class)->group(function(){
            Route::get('/', 'index')->name('dashboard');
            Route::get('/export-sessions', 'exportSessions')->name('dashboard.export');
        });

        // User management
        Route::resource('/users', UserController::class);

        Route::resource('/events', EventController::class)->except(['show']);

        Route::resource('/bookings', BookingController::class)->only(['index', 'destroy']);

        Route::prefix('/payments')->controller(PaymentController::class)->group(function(){
            Route::get('/history', 'paymentHistory')->name('payments.history');
        });

        // Payment Gateway Settings
        Route::name('payment-gateway.')->controller(PaymentGatewayController::class)->group(function(){
            Route::get('/payment-gateway', 'edit')->name('edit');
            Route::put('/payment-gateway', 'update')->name('update');
        });
    });
  });






});





