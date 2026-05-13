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
    Route::put('/profile/notification-preferences', [\App\Http\Controllers\ProfileController::class, 'updateNotificationPreferences'])->name('profile.notification-preferences');
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

    Route::post('/waitlists', [\App\Http\Controllers\WaitlistController::class, 'store'])->name('waitlists.store');
    Route::delete('/waitlists/{waitingList}', [\App\Http\Controllers\WaitlistController::class, 'destroy'])->name('waitlists.destroy');
    Route::get('/my/waitlists', [\App\Http\Controllers\WaitlistController::class, 'myWaitlists'])->name('my.waitlists');

    Route::post('/books/{book}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [\App\Http\Controllers\ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [\App\Http\Controllers\ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::post('/authors/{author}/favourite', [\App\Http\Controllers\FavouriteAuthorController::class, 'store'])->name('authors.favourite');
    Route::delete('/authors/{author}/favourite', [\App\Http\Controllers\FavouriteAuthorController::class, 'destroy'])->name('authors.unfavourite');

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::patch('/borrowings/{borrowing}/return', [\App\Http\Controllers\Admin\AdminBorrowingController::class, 'processReturn'])->name('borrowings.return');
    Route::get('/books/{book}/waitlists', [\App\Http\Controllers\Admin\AdminWaitlistController::class, 'index'])->name('books.waitlists');
    Route::delete('/waitlists/{waitingList}', [\App\Http\Controllers\Admin\AdminWaitlistController::class, 'destroy'])->name('waitlists.destroy');
    Route::post('/books', [\App\Http\Controllers\Admin\AdminBookController::class, 'store'])->name('books.store');
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
