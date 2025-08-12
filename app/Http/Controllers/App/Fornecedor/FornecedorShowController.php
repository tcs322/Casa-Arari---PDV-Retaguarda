<?php

namespace App\Http\Controllers\App\Fornecedor;

use App\Actions\Fornecedor\FornecedorShowAction;
use App\DTO\Fornecedor\FornecedorShowDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Fornecedor\FornecedorShowRequest;

class FornecedorShowController extends Controller
{
    public function __construct(
        protected FornecedorShowAction $storeAction
    ) {}

    public function show(string $uuid, FornecedorShowRequest $storeRequest)
    {
        $storeRequest->merge([
            "uuid" => $uuid
        ]);

        $fornecedor = $this->storeAction->exec(FornecedorShowDTO::makeFromRequest($storeRequest));
        
        return view('app.fornecedor.show', [
            "fornecedor" => $fornecedor
        ]);
    }
}
