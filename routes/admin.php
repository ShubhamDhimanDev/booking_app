<?php
// routes/admin.php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\TrackingSettingsController;
use App\Http\Controllers\Admin\RefundController;
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
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'adminCancelBooking'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/send-followup', [BookingController::class, 'sendFollowUpInvite'])->name('bookings.send-followup');

        // Refunds Management
        Route::prefix('/refunds')->name('refunds.')->controller(RefundController::class)->group(function(){
            Route::get('/', 'index')->name('index');
            Route::get('/{refund}', 'show')->name('show');
            Route::post('/{refund}/retry', 'retry')->name('retry');
            Route::get('/export', 'export')->name('export');
        });

        Route::prefix('/payments')->controller(PaymentController::class)->group(function(){
            Route::get('/history', 'paymentHistory')->name('payments.history');
        });

        // Payment Gateway Settings
        Route::name('payment-gateway.')->controller(PaymentGatewayController::class)->group(function(){
            Route::get('/payment-gateway', 'edit')->name('edit');
            Route::put('/payment-gateway', 'update')->name('update');
        });

        // Tracking Settings
        Route::name('tracking.')->controller(TrackingSettingsController::class)->group(function(){
            Route::get('/tracking-settings', 'index')->name('index');
            Route::put('/tracking-settings', 'update')->name('update');
        });

        // Promo Codes
        Route::resource('/promo-codes', PromoCodeController::class);
    });
  });






});





