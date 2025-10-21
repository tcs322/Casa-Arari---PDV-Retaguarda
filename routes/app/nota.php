<?php

Route::get('nota', [App\Http\Controllers\App\Nota\NotaController::class, 'index'])->name('nota.index');
Route::get('nota/create', [App\Http\Controllers\App\Nota\NotaController::class, 'create'])->name('nota.create');
Route::get('nota/create-without-xml', [App\Http\Controllers\App\Nota\NotaController::class, 'createWithoutXml'])->name('nota.create-without-xml');
Route::post('nota', [App\Http\Controllers\App\Nota\NotaController::class, 'store'])->name('nota.store');
Route::get('nota/{uuid}/show', [App\Http\Controllers\App\Nota\NotaController::class, 'show'])->name('nota.show');
