<?php

Route::get('produto', [App\Http\Controllers\App\Product\ProductController::class, 'index'])->name('produto.index');
Route::get('produto/create', [App\Http\Controllers\App\Product\ProductController::class, 'create'])->name('produto.create');
Route::post('produto', [App\Http\Controllers\App\Product\ProductController::class, 'store'])->name('produto.store');
Route::get('produto/create-many', [App\Http\Controllers\App\Product\ProductController::class, 'createManyByXml'])->name('produto.create-many');
