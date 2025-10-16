<?php

Route::get('clientes', [App\Http\Controllers\App\Cliente\ClienteController::class, 'index'])->name('cliente.index');
Route::get('cliente/create', [App\Http\Controllers\App\Cliente\ClienteController::class, 'create'])->name('cliente.create');