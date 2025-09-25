<?php

Route::get('frente-caixa', [\App\Http\Controllers\App\Venda\VendaController::class, 'create'])->name('frente-caixa');
Route::get('frente-caixa/pagamento', [\App\Http\Controllers\App\Venda\VendaController::class, 'pagamento'])->name('frente-caixa.pagamento');