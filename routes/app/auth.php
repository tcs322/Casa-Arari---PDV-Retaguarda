<?php

use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('auth.login.form');
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');

Route::middleware(['auth', 'force_password_change'])->group(function () {
    Route::get('/dashboard', function () {
        return view('app.dashboard.index');
    })->name('dashboard.index');
});

// troca de senha

Route::middleware(['auth'])->group(function () {
    Route::get('/password/change', [ChangePasswordController::class, 'showForm'])
        ->name('auth.password.change.form');

    Route::post('/password/change', [ChangePasswordController::class, 'update'])
        ->name('auth.password.change');
});
