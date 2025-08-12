<?php

Route::get('/', [\App\Http\Controllers\App\Dashboard\DashboardIndexController::class, 'index'])->name('dashboard.index');
Route::post('/auth/login', [\App\Http\Controllers\Auth\AuthLoginController::class, 'login'])->name('auth.login');
Route::get('/auth/open-request-password-change', [\App\Http\Controllers\Auth\AuthLoginController::class, 'index'])->name('auth.open-request-password-change');
Route::post('/auth/send-request-password-change', [\App\Http\Controllers\Auth\AuthLoginController::class, 'index'])->name('auth.send-request-password-change');
Route::get('/auth/open-password-change', [\App\Http\Controllers\Auth\AuthLoginController::class, 'index'])->name('auth.open-password-change');
Route::post('/auth/send-password-change', [\App\Http\Controllers\Auth\AuthLoginController::class, 'index'])->name('auth.send-password-change');

Route::middleware(['auth.basic'])->group(function() {
    Route::get('/auth/logout', function() {  Auth::logout(); return redirect()->route('auth.index'); })->name('auth.logout');
});
