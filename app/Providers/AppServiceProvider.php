<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Event;
use App\Models\Booking;
use App\Models\User;
use App\Observers\EventObserver;
use App\Observers\BookingObserver;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();

        // Register model observers for usage tracking
        Event::observe(EventObserver::class);
        Booking::observe(BookingObserver::class);
        User::observe(UserObserver::class);
    }
}
