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
| Main Route
|--------------------------------------------------------------------------
*/

Route::get('/', [\App\Http\Controllers\HomeController::class, 'root']);
