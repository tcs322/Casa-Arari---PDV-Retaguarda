<?php

Route::get('clientes', [App\Http\Controllers\App\Cliente\ClienteController::class, 'index'])->name('cliente.index');
Route::get('cliente/create', [App\Http\Controllers\App\Cliente\ClienteController::class, 'create'])->name('cliente.create');
Route::post('cliente', [App\Http\Controllers\App\Cliente\ClienteController::class, 'store'])->name('cliente.store');
Route::get('cliente/{uuid}/edit', [App\Http\Controllers\App\Cliente\ClienteController::class, 'edit'])->name('cliente.edit');
Route::put('cliente/update', [App\Http\Controllers\App\Cliente\ClienteController::class, 'update'])->name('cliente.update');
Route::get('cliente/{uuid}/show', [App\Http\Controllers\App\Cliente\ClienteController::class, 'show'])->name('cliente.show');
