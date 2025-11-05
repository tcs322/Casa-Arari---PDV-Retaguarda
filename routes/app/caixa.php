<?php

Route::post('caixa/abrir', [App\Http\Controllers\App\Caixa\CaixaController::class, 'abrirCaixa'])->name('caixa.abrir');
Route::post('caixa/fechar', [App\Http\Controllers\App\Caixa\CaixaController::class, 'fecharCaixa'])->name('caixa.fechar');