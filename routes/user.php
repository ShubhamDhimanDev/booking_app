<?php

use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\EventController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User/Guest Routes
|--------------------------------------------------------------------------
|
| Public-facing routes for guests to view available events and create
| bookings. These routes are typically accessed via organization subdomains
| or custom domains.
|
*/

// Public event viewing and booking routes
Route::middleware(['web'])
    ->name('user.')
    ->group(function () {

        // View available events for an organization
        Route::get('/{organization:subdomain}/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/{organization:subdomain}/events/{event:slug}', [EventController::class, 'show'])->name('events.show');

        // Booking flow
        Route::get('/{organization:subdomain}/events/{event:slug}/book', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('/{organization:subdomain}/events/{event:slug}/book', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}/confirmation', [BookingController::class, 'confirmation'])->name('bookings.confirmation');

        // Booking management (for guests via email link)
        Route::get('/bookings/{booking}/manage', [BookingController::class, 'manage'])->name('bookings.manage');
        Route::post('/bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('bookings.reschedule');
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

        // Payment callback routes
        Route::get('/payments/callback/{gateway}', [BookingController::class, 'paymentCallback'])->name('payments.callback');
        Route::post('/payments/webhook/{gateway}', [BookingController::class, 'paymentWebhook'])->name('payments.webhook');
    });

// Authenticated user dashboard (if implementing user accounts for guests)
Route::middleware(['auth', 'verified'])
    ->prefix('my')
    ->name('user.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('user.dashboard');
        })->name('dashboard');

        Route::get('/bookings', [BookingController::class, 'userBookings'])->name('bookings.list');
    });
