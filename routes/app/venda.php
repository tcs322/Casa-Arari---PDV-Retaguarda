<?php

use Illuminate\Support\Facades\Route;

Route::get('/vendas', [\App\Http\Controllers\App\Venda\VendaController::class, 'index'])->name('venda.index');
Route::get('/vendas/{uuid}/show', [\App\Http\Controllers\App\Venda\VendaController::class, 'show'])->name('venda.show');
Route::post('/venda/{uuid}/cancelar', [\App\Http\Controllers\App\Venda\VendaController::class, 'cancelarVenda'])->name('venda.cancelar');

Route::middleware(['caixa_aberto'])->group(function () {
    Route::get('frente-caixa', [\App\Http\Controllers\App\Venda\VendaController::class, 'create'])->name('frente-caixa');
    Route::get('frente-caixa/pagamento', [\App\Http\Controllers\App\Venda\VendaController::class, 'pagamento'])->name('frente-caixa.pagamento');
});

// venda itens

// Formulário de busca
Route::get('/venda-itens/search', [\App\Http\Controllers\App\VendaItem\VendaItemController::class, 'search'])->name('venda_itens.search');

// Resultado da busca
Route::get('/venda-itens/period', [\App\Http\Controllers\App\VendaItem\VendaItemController::class, 'getByPeriod'])->name('venda_itens.by_period');

// Exportar PDF
Route::get('/venda-itens/period/pdf', [\App\Http\Controllers\App\VendaItem\VendaItemController::class, 'exportPdf'])
    ->name('venda_itens.by_period_pdf');
