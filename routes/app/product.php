<?php

Route::get('produto', [App\Http\Controllers\App\Product\ProductController::class, 'index'])->name('produto.index')->withoutMiddleware(['is_admin']);
Route::get('produto/create', [App\Http\Controllers\App\Product\ProductController::class, 'create'])->name('produto.create');
Route::post('produto', [App\Http\Controllers\App\Product\ProductController::class, 'store'])->name('produto.store');
Route::get('produto/create-many', [App\Http\Controllers\App\Product\ProductController::class, 'createManyByXml'])->name('produto.create-many');
Route::get('produto/{uuid}/edit', [App\Http\Controllers\App\Product\ProductController::class, 'edit'])->name('produto.edit');
Route::put('produto/{uuid}/update', [App\Http\Controllers\App\Product\ProductController::class, 'update'])->name('produto.update');
Route::get('produto/{uuid}/show', [App\Http\Controllers\App\Product\ProductController::class, 'show'])->name('produto.show');
