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
    Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/books', [\App\Http\Controllers\Admin\AdminBookController::class, 'index'])->name('books.index');
    Route::get('/books/create', [\App\Http\Controllers\Admin\AdminBookController::class, 'create'])->name('books.create');
    Route::post('/books', [\App\Http\Controllers\Admin\AdminBookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [\App\Http\Controllers\Admin\AdminBookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [\App\Http\Controllers\Admin\AdminBookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [\App\Http\Controllers\Admin\AdminBookController::class, 'destroy'])->name('books.destroy');
    Route::get('/books/{book}/waitlists', [\App\Http\Controllers\Admin\AdminWaitlistController::class, 'index'])->name('books.waitlists');

    Route::get('/authors', [\App\Http\Controllers\Admin\AdminAuthorController::class, 'index'])->name('authors.index');
    Route::get('/authors/create', [\App\Http\Controllers\Admin\AdminAuthorController::class, 'create'])->name('authors.create');
    Route::post('/authors', [\App\Http\Controllers\Admin\AdminAuthorController::class, 'store'])->name('authors.store');
    Route::get('/authors/{author}/edit', [\App\Http\Controllers\Admin\AdminAuthorController::class, 'edit'])->name('authors.edit');
    Route::put('/authors/{author}', [\App\Http\Controllers\Admin\AdminAuthorController::class, 'update'])->name('authors.update');
    Route::delete('/authors/{author}', [\App\Http\Controllers\Admin\AdminAuthorController::class, 'destroy'])->name('authors.destroy');

    Route::get('/genres', [\App\Http\Controllers\Admin\AdminGenreController::class, 'index'])->name('genres.index');
    Route::get('/genres/create', [\App\Http\Controllers\Admin\AdminGenreController::class, 'create'])->name('genres.create');
    Route::post('/genres', [\App\Http\Controllers\Admin\AdminGenreController::class, 'store'])->name('genres.store');
    Route::get('/genres/{genre}/edit', [\App\Http\Controllers\Admin\AdminGenreController::class, 'edit'])->name('genres.edit');
    Route::put('/genres/{genre}', [\App\Http\Controllers\Admin\AdminGenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{genre}', [\App\Http\Controllers\Admin\AdminGenreController::class, 'destroy'])->name('genres.destroy');

    Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{user}/activate', [\App\Http\Controllers\Admin\AdminUserController::class, 'activate'])->name('users.activate');
    Route::patch('/users/{user}/role', [\App\Http\Controllers\Admin\AdminUserController::class, 'updateRole'])->name('users.role');

    Route::get('/borrowings', [\App\Http\Controllers\Admin\AdminBorrowingController::class, 'index'])->name('borrowings.index');
    Route::patch('/borrowings/{borrowing}/return', [\App\Http\Controllers\Admin\AdminBorrowingController::class, 'processReturn'])->name('borrowings.return');

    Route::delete('/waitlists/{waitingList}', [\App\Http\Controllers\Admin\AdminWaitlistController::class, 'destroy'])->name('waitlists.destroy');

    Route::get('/reports', [\App\Http\Controllers\Admin\AdminReportController::class, 'index'])->name('reports.index');
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
