<?php

use Illuminate\Support\Facades\Route;

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

// Public landing page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Redirect authenticated users based on their role
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('super-admin')) {
        return redirect()->route('super-admin.dashboard');
    }

    if ($user->organization_id) {
        return redirect()->route('organization.dashboard');
    }

    return redirect()->route('user.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
