<?php

namespace App\Http\Controllers\App\Fornecedor;

use App\Actions\Fornecedor\FornecedorStoreAction;
use App\DTO\Fornecedor\FornecedorStoreDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Fornecedor\FornecedorStoreRequest;

class FornecedorStoreController extends Controller
{
    public function __construct(
        protected FornecedorStoreAction $storeAction
    ) {}

    public function store(FornecedorStoreRequest $storeRequest)
    {
        $this->storeAction->exec(FornecedorStoreDTO::makeFromRequest($storeRequest));

        return redirect()->route('fornecedor.index')->with('message', 'Registro criado');
    }
}
