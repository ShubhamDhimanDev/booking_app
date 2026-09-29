<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

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

        // Booking-flow pages (slot selection, details, payment, thank-you...) share the
        // country's header/footer/timezone default: resolve it from the event/booking in scope.
        View::composer('layouts.app', function ($view) {
            $data = $view->getData();
            $event = $data['event'] ?? optional($data['booking'] ?? null)->event ?? null;
            $view->with('cmsCountry', $event ? $event->country : null);
        });
    }
}
