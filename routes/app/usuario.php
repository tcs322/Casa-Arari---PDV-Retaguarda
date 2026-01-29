<?php

Route::get('/usuario/show/{uuid}', [\App\Http\Controllers\App\Usuario\UsuarioShowController::class, 'show'])->name('usuario.show');
Route::get('/usuario/create', [\App\Http\Controllers\App\Usuario\UsuarioCreateController::class, 'create'])->name('usuario.create');
Route::put('/usuario/{user}', [\App\Http\Controllers\App\Usuario\UsuarioUpdateController::class, 'update'])->name('usuario.update');
Route::get('/usuario', [\App\Http\Controllers\App\Usuario\UsuarioIndexController::class, 'index'])->name('usuario.index');
Route::get('/usuario/edit/{user}', [\App\Http\Controllers\App\Usuario\UsuarioEditController::class, 'edit'])->name('usuario.edit');
Route::post('/usuario', [\App\Http\Controllers\App\Usuario\UsuarioStoreController::class, 'store'])->name('usuario.store');
Route::post('/usuario/{uuid}/reset', [\App\Http\Controllers\App\Usuario\UsuarioResetController::class, 'reset'])->name('usuario.reset');