<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayuController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TransactionsController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\LinkedWithGoogleMiddleware;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::redirect('/', '/events')->name('dashboard');

Route::middleware('auth')->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  Route::middleware([IsAdmin::class, LinkedWithGoogleMiddleware::class])->group(function(){
    // Route::resource('/events', EventController::class)
    //   ->middleware(['role:admin'])
    //   ->except(['show']);
    // Route::resource('/bookings', BookingController::class)->only(['index', 'destroy']);
  });

  // Transactions for regular users
  Route::get('/user/transactions', [TransactionsController::class, 'index'])->name('transactions.index');
  // Bookings for regular users (bookers)
  Route::get('/user/bookings', [BookingController::class, 'userIndex'])->name('user.bookings.index');
  Route::get('/user/bookings/{booking}/reschedule', [BookingController::class, 'showRescheduleForm'])->name('user.bookings.reschedule.form');
  Route::post('/user/bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('user.bookings.reschedule');
});

Route::get('/payment/{booking?}', [PaymentController::class, 'showPaymentPage'])->name('payment.page');
Route::get('/payment/thankyou/{booking}', [PaymentController::class, 'thankYouPage'])->name('payment.thankyou');
Route::post('/create-order', [PaymentController::class, 'createOrder']);
Route::post('/verify-payment', [PaymentController::class, 'verifyPayment']);

Route::get('/e/{event:slug}', [EventController::class, 'showPublic'])->name('events.show.public');
Route::post('/e/{event:slug}/book', [BookingController::class, 'store'])->name('bookings.store');

Route::get('/test', [TestController::class, 'index'])->name('test.index');

Route::get('/payu/payment', [PayuController::class, 'paymentForm'])->name('payu.paymentForm');
Route::post('/payu/success', [PayuController::class, 'paymentSuccess'])->name('payu.success');
Route::post('/payu/failure', [PayuController::class, 'paymentFailure'])->name('payu.failure');

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
