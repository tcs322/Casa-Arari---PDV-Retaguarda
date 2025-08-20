<?php

use App\Http\Controllers\App\Dashboard\DashboardIndexController;
use Illuminate\Support\Facades\Route;

Route::get('product', [App\Http\Controllers\App\Product\ProductController::class, 'index'])->name('product.index');
Route::get('product/create', [App\Http\Controllers\App\Product\ProductController::class, 'create'])->name('product.create');
Route::post('product', [App\Http\Controllers\App\Product\ProductController::class, 'store'])->name('product.store');
Route::get('product/create-many', [App\Http\Controllers\App\Product\ProductController::class, 'createManyByXml'])->name('product.create-many');


Route::middleware(['auth'])->group(function() {
    Route::get('/dashboard', [DashboardIndexController::class, 'index'])->name('dashboard.index');
});
