<?php

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\CountryPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public CMS routes (country-scoped)
|--------------------------------------------------------------------------
| Loaded last from routes/web.php. `{cmsCountry}` is bound to a Country slug
| in RouteServiceProvider; unknown slugs 404.
*/

Route::get('/{cmsCountry}', [CountryPageController::class, 'home'])->name('country.home');
Route::get('/{cmsCountry}/e/{event:slug}', [EventController::class, 'showInCountry'])->withoutScopedBindings()->middleware('event.country')->name('country.event');
Route::get('/{cmsCountry}/{slug}', [CountryPageController::class, 'show'])->name('country.page');
