<?php
// routes/admin.php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\NavigationController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\TrackingSettingsController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\BookingController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\LinkedWithGoogleMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PaymentGatewayController;


Route::prefix('admin')->name('admin.')->group(function(){

  Route::middleware(['auth', IsAdmin::class])->group(function(){

    Route::middleware(LinkedWithGoogleMiddleware::class)->group(function(){
        Route::controller(DashboardController::class)->group(function(){
            Route::get('/', 'index')->name('dashboard');
            Route::get('/export-sessions', 'exportSessions')->name('dashboard.export');
        });

        // User management
        Route::get('/users/{user}', fn ($user) => redirect()->route('admin.users.edit', $user))->whereNumber('user')->name('users.show');
        Route::resource('/users', UserController::class)->except(['show']);

        Route::resource('/events', EventController::class)->except(['show']);

        Route::resource('/bookings', BookingController::class)->only(['index', 'destroy']);
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'adminCancelBooking'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/request-reschedule', [BookingController::class, 'adminRequestReschedule'])->name('bookings.request-reschedule');
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
        Route::get('/promo-codes/{promo_code}', fn ($promo_code) => redirect()->route('admin.promo-codes.edit', $promo_code))->whereNumber('promo_code')->name('promo-codes.show');
        Route::resource('/promo-codes', PromoCodeController::class)->except(['show']);

        // Store catalog
        Route::resource('/products', ProductController::class)->except(['show']);
        Route::resource('/product-categories', ProductCategoryController::class)->except(['show']);

        // Store orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/slip', [OrderController::class, 'slip'])->name('orders.slip');
        Route::post('/orders/{order}/status', [OrderController::class, 'status'])->name('orders.status');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
        Route::post('/orders/{order}/resend', [OrderController::class, 'resend'])->name('orders.resend');

        // CMS: countries, per-country menus, pages and sections
        Route::resource('/countries', CountryController::class)->except(['show']);
        Route::put('/countries/{country}/navigation', [NavigationController::class, 'update'])->name('countries.navigation');

        Route::resource('/pages', PageController::class)->except(['show']);
        Route::post('/pages/{page}/duplicate', [PageController::class, 'duplicate'])->name('pages.duplicate');
        Route::post('/pages/{page}/sections', [PageSectionController::class, 'store'])->name('pages.sections.store');
        Route::post('/pages/{page}/sections/reorder', [PageSectionController::class, 'reorder'])->name('pages.sections.reorder');
        Route::put('/sections/{section}', [PageSectionController::class, 'update'])->name('sections.update');
        Route::delete('/sections/{section}', [PageSectionController::class, 'destroy'])->name('sections.destroy');
        Route::post('/cms/upload', [PageSectionController::class, 'upload'])->name('cms.upload');

        // Old homepage editor -> the page builder
        Route::redirect('/homepage-settings', '/admin/pages')->name('homepage-settings.index');
    });
  });






});





