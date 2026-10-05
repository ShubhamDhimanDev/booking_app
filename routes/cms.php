<?php

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CountryPageController;
use App\Http\Controllers\OrderPaymentController;
use App\Http\Controllers\StoreController;
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
// Store. Must stay above the `/{cmsCountry}/{slug}` catch-all (store/cart/checkout are reserved slugs).
Route::get('/{cmsCountry}/store', [StoreController::class, 'index'])->name('store.index');
Route::get('/{cmsCountry}/store/p/{slug}', [StoreController::class, 'show'])->name('store.show');
Route::get('/{cmsCountry}/cart', [CartController::class, 'show'])->name('cart.show');
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/{cmsCountry}/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/{cmsCountry}/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/{cmsCountry}/cart/remove/{productId}', [CartController::class, 'remove'])->whereNumber('productId')->name('cart.remove');
    Route::post('/{cmsCountry}/cart/promo', [CartController::class, 'applyPromo'])->name('cart.promo');
    Route::post('/{cmsCountry}/cart/promo/remove', [CartController::class, 'removePromo'])->name('cart.promo.remove');
});

Route::get('/{cmsCountry}/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/{cmsCountry}/checkout', [CheckoutController::class, 'place'])->middleware('throttle:20,1')->name('checkout.place');

// Order pages: signed URLs, so guests can use them without an account.
Route::middleware('signed')->group(function () {
    Route::get('/{cmsCountry}/order/{orderNumber}/pay', [OrderPaymentController::class, 'pay'])->name('order.pay');
    Route::post('/{cmsCountry}/order/{orderNumber}/razorpay-verify', [OrderPaymentController::class, 'razorpayVerify'])->name('order.razorpay.verify');
    Route::get('/{cmsCountry}/order/{orderNumber}/thank-you', [OrderPaymentController::class, 'thankYou'])->name('order.thankyou');
    Route::get('/{cmsCountry}/order/{orderNumber}/failed', [OrderPaymentController::class, 'failed'])->name('order.failed');
});

Route::get('/{cmsCountry}/{slug}', [CountryPageController::class, 'show'])->name('country.page');
