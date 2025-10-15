<?php

Route::get('frente-caixa', [\App\Http\Controllers\App\Venda\VendaController::class, 'create'])->name('frente-caixa');
Route::get('frente-caixa/pagamento', [\App\Http\Controllers\App\Venda\VendaController::class, 'pagamento'])->name('frente-caixa.pagamento');
Route::get('/vendas', [\App\Http\Controllers\App\Venda\VendaController::class, 'index'])->name('venda.index');
Route::get('/vendas/{uuid}/show', [\App\Http\Controllers\App\Venda\VendaController::class, 'show'])->name('venda.show');