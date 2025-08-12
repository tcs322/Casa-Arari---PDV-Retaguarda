<?php

namespace App\Http\Controllers\App\Fornecedor;

use App\Actions\Fornecedor\FornecedorUpdateAction;
use App\DTO\Fornecedor\FornecedorUpdateDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Fornecedor\FornecedorUpdateRequest;

class FornecedorUpdateController extends Controller
{
    public function __construct(
        protected FornecedorUpdateAction $updateAction
    ) {}

    public function update(FornecedorUpdateRequest $updateRequest)
    {
        $this->updateAction->exec(FornecedorUpdateDTO::makeFromRequest($updateRequest));

        return redirect()->route('fornecedor.index')->with('message', 'Registro atualizado');
    }
}
