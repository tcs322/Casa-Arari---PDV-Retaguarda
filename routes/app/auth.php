<?php

// Route::get('/', [\App\Http\Controllers\App\Dashboard\DashboardIndexController::class, 'index'])->name('dashboard.index');
// Route::post('/auth/login', [\App\Http\Controllers\Auth\AuthLoginController::class, 'login'])->name('auth.login');
// Route::get('/auth/open-request-password-change', [\App\Http\Controllers\Auth\AuthLoginController::class, 'index'])->name('auth.open-request-password-change');
// Route::post('/auth/send-request-password-change', [\App\Http\Controllers\Auth\AuthLoginController::class, 'index'])->name('auth.send-request-password-change');
// Route::get('/auth/open-password-change', [\App\Http\Controllers\Auth\AuthLoginController::class, 'index'])->name('auth.open-password-change');
// Route::post('/auth/send-password-change', [\App\Http\Controllers\Auth\AuthLoginController::class, 'index'])->name('auth.send-password-change');

// Route::middleware(['auth.basic'])->group(function() {
//     Route::get('/auth/logout', function() {  Auth::logout(); return redirect()->route('dashboard.index'); })->name('auth.logout');
// });

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('auth.login.form');
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('app.dashboard.index');
    })->name('dashboard.index');
});
