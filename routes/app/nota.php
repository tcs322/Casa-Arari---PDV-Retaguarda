<?php

Route::get('nota', [App\Http\Controllers\App\Nota\NotaController::class, 'index'])->name('nota.index');
Route::get('nota/create', [App\Http\Controllers\App\Nota\NotaController::class, 'create'])->name('nota.create');