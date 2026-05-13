<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Route
|--------------------------------------------------------------------------
*/

Route::get('login', [\App\Http\Controllers\AuthController::class, 'login'])->name('auth.login');
Route::post('login', [\App\Http\Controllers\AuthController::class, 'signIn'])->name('auth.signIn');
Route::get('register', [\App\Http\Controllers\AuthController::class, 'register'])->name('auth.register');
Route::post('register', [\App\Http\Controllers\AuthController::class, 'signUp'])->name('auth.signUp');
Route::post('logout', [\App\Http\Controllers\AuthController::class, 'signOut'])->name('auth.signOut');

/*
|--------------------------------------------------------------------------
| Google OAuth
|--------------------------------------------------------------------------
*/

Route::get('auth/google', [\App\Http\Controllers\SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [\App\Http\Controllers\SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');

/*
|--------------------------------------------------------------------------
| Profile (auth required)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'changePassword'])->name('profile.password');
});

/*
|--------------------------------------------------------------------------
| Main Route
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Borrowing Routes (member)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/borrowings', [\App\Http\Controllers\BorrowingController::class, 'store'])->name('borrowings.store');
    Route::patch('/borrowings/{borrowing}/return', [\App\Http\Controllers\BorrowingController::class, 'processReturn'])->name('borrowings.return');
    Route::patch('/borrowings/{borrowing}/renew', [\App\Http\Controllers\BorrowingController::class, 'renew'])->name('borrowings.renew');
    Route::get('/my/borrowings', [\App\Http\Controllers\BorrowingController::class, 'myBorrowings'])->name('my.borrowings');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::patch('/borrowings/{borrowing}/return', [\App\Http\Controllers\Admin\AdminBorrowingController::class, 'processReturn'])->name('borrowings.return');
});

/*
|--------------------------------------------------------------------------
| Main Route
|--------------------------------------------------------------------------
*/

Route::get('/', [\App\Http\Controllers\HomeController::class, 'home'])->name('home');
Route::get('/explore', [\App\Http\Controllers\BookController::class, 'index'])->name('explore');
Route::get('/books', [\App\Http\Controllers\BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [\App\Http\Controllers\BookController::class, 'show'])->name('books.show');
