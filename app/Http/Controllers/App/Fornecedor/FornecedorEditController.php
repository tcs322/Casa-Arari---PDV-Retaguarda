<?php

namespace App\Http\Controllers\App\Fornecedor;

use App\Actions\Fornecedor\FornecedorCreateAction;
use App\Actions\Fornecedor\FornecedorEditAction;
use App\DTO\Fornecedor\FornecedorEditDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Fornecedor\FornecedorEditRequest;

class FornecedorEditController extends Controller
{
    public function __construct(
        protected FornecedorEditAction $editAction
    ) { }

    public function edit(string $uuid, FornecedorEditRequest $editRequest)
    {
        $editRequest->merge([
            "uuid" => $uuid
        ]);

        $formData = (new FornecedorCreateAction())->exec();

        $fornecedor = $this->editAction->exec(FornecedorEditDTO::makeFromRequest($editRequest));

        return view('app.fornecedor.edit', [
            "fornecedor" => $fornecedor,
            "formData" => $formData
        ]);
    }
}
