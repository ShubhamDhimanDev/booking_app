<?php

use App\Http\Controllers\Organization\DashboardController;
use App\Http\Controllers\Organization\EventController;
use App\Http\Controllers\Organization\BookingController;
use App\Http\Controllers\Organization\TeamController;
use App\Http\Controllers\Organization\SubscriptionController;
use App\Http\Controllers\Organization\InvoiceController;
use App\Http\Controllers\Organization\PaymentController;
use App\Http\Controllers\Organization\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Organization Routes
|--------------------------------------------------------------------------
|
| Routes for organization members to manage their events, bookings, team,
| subscriptions, and settings. All routes require authentication and
| organization membership.
|
*/

Route::middleware(['auth', 'verified', 'has.organization'])
    ->prefix('organization')
    ->name('organization.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Events Management
        Route::resource('events', EventController::class);
        Route::post('events/{event}/duplicate', [EventController::class, 'duplicate'])->name('events.duplicate');
        Route::patch('events/{event}/toggle-status', [EventController::class, 'toggleStatus'])->name('events.toggle-status');

        // Bookings Management
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        Route::post('bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('bookings.reschedule');

        // Payments
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');

        // Team Management
        Route::get('team', [TeamController::class, 'index'])->name('team.index');
        Route::get('team/invite', [TeamController::class, 'showInviteForm'])->name('team.invite');
        Route::post('team/invite', [TeamController::class, 'invite'])->name('team.invite.send');
        Route::get('team/{user}', [TeamController::class, 'show'])->name('team.show');
        Route::get('team/{user}/edit', [TeamController::class, 'edit'])->name('team.edit');
        Route::patch('team/{user}', [TeamController::class, 'update'])->name('team.update');
        Route::delete('team/{user}', [TeamController::class, 'remove'])->name('team.remove');

        // Subscription Management
        Route::get('subscription', [SubscriptionController::class, 'show'])->name('subscription');
        Route::get('subscription/change-plan', [SubscriptionController::class, 'changePlan'])->name('subscription.change-plan');
        Route::get('subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
        Route::post('subscription/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
        Route::post('subscription/payment-callback', [SubscriptionController::class, 'paymentCallback'])->name('subscription.payment-callback');
        Route::post('subscription/change', [SubscriptionController::class, 'change'])->name('subscription.change');
        Route::post('subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
        Route::post('subscription/resume', [SubscriptionController::class, 'resume'])->name('subscription.resume');
        Route::post('subscription/payment-method', [SubscriptionController::class, 'updatePaymentMethod'])->name('subscription.payment-method');

        // Invoices
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

        // Settings
        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::patch('settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general');
        Route::patch('settings/branding', [SettingsController::class, 'updateBranding'])->name('settings.branding');
        Route::patch('settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications');
        Route::delete('settings/delete-organization', [SettingsController::class, 'deleteOrganization'])->name('settings.delete');
    });
