<?php

use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\OrganizationController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\EventController;
use App\Http\Controllers\SuperAdmin\BookingController;
use App\Http\Controllers\SuperAdmin\SubscriptionPlanController;
use App\Http\Controllers\SuperAdmin\SubscriptionController;
use App\Http\Controllers\SuperAdmin\InvoiceController;
use App\Http\Controllers\SuperAdmin\ReportController;
use App\Http\Controllers\SuperAdmin\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
|
| Routes for super administrators to manage the entire platform including
| organizations, users, subscription plans, and system settings.
|
*/

Route::middleware(['auth', 'verified', 'role:super-admin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Organizations Management
        Route::get('organizations', [OrganizationController::class, 'index'])->name('organizations.index');
        Route::get('organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
        Route::post('organizations', [OrganizationController::class, 'store'])->name('organizations.store');
        Route::get('organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
        Route::get('organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
        Route::patch('organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
        Route::delete('organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
        Route::post('organizations/{organization}/suspend', [OrganizationController::class, 'suspend'])->name('organizations.suspend');
        Route::post('organizations/{organization}/activate', [OrganizationController::class, 'activate'])->name('organizations.activate');

        // Users Management
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');

        // All Events (Platform-wide)
        Route::get('events', [EventController::class, 'index'])->name('events.index');
        Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
        Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

        // All Bookings (Platform-wide)
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');

        // Subscription Plans Management
        Route::get('plans', [SubscriptionPlanController::class, 'index'])->name('plans.index');
        Route::get('plans/create', [SubscriptionPlanController::class, 'create'])->name('plans.create');
        Route::post('plans', [SubscriptionPlanController::class, 'store'])->name('plans.store');
        Route::get('plans/{plan}', [SubscriptionPlanController::class, 'show'])->name('plans.show');
        Route::get('plans/{plan}/edit', [SubscriptionPlanController::class, 'edit'])->name('plans.edit');
        Route::patch('plans/{plan}', [SubscriptionPlanController::class, 'update'])->name('plans.update');
        Route::delete('plans/{plan}', [SubscriptionPlanController::class, 'destroy'])->name('plans.destroy');
        Route::post('plans/{plan}/activate', [SubscriptionPlanController::class, 'activate'])->name('plans.activate');
        Route::post('plans/{plan}/deactivate', [SubscriptionPlanController::class, 'deactivate'])->name('plans.deactivate');

        // Subscriptions Management
        Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        Route::post('subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');

        // Invoices Management
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports');
        Route::get('reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('reports/subscriptions', [ReportController::class, 'subscriptions'])->name('reports.subscriptions');
        Route::get('reports/organizations', [ReportController::class, 'organizations'])->name('reports.organizations');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

        // System Settings
        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::patch('settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general');
        Route::patch('settings/email', [SettingsController::class, 'updateEmail'])->name('settings.email');
        Route::patch('settings/payment', [SettingsController::class, 'updatePayment'])->name('settings.payment');
        Route::post('settings/cache-clear', [SettingsController::class, 'clearCache'])->name('settings.cache-clear');
    });
