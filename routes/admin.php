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

  Route::controller(AuthController::class)->group(function(){
      Route::match(['get', 'post'], '/login', 'login')->name('login');
      Route::any('/logout', 'logout')->name('logout');


  });

  Route::controller(GoogleAuthController::class)->group(function(){
      Route::get('/auth/google', 'googleAuth')->name('google.auth');
      Route::get('/auth/google/redirect', 'googleAuthRedirect')->name('google.redirect');
      Route::get('/auth/google/callback', 'googleAuthCallback')->name('google.callback');
  });


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

        Route::prefix('payments')->controller(PaymentController::class)->group(function(){
            Route::get('/history', 'paymentHistory')->name('payments.history');
        });

        // Payment Gateway Settings

        Route::get('payment-gateway', [PaymentGatewayController::class, 'edit'])->name('payment-gateway.edit');
        Route::put('payment-gateway', [PaymentGatewayController::class, 'update'])->name('payment-gateway.update');

    });
  });






});





