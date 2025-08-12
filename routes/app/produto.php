<?php

Route::get('produto', [App\Http\Controllers\App\Produto\ProdutoIndexController::class, 'index'])->name('produto.index');
Route::get('produto/create', [App\Http\Controllers\App\Produto\ProdutoCreateController::class, 'create'])->name('produto.create');
Route::post('produto', [App\Http\Controllers\App\Produto\ProdutoStoreController::class, 'store'])->name('produto.store');