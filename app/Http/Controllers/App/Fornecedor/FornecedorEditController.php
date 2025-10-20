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
        protected FornecedorEditAction $storeAction
    ) { }

    public function edit(string $uuid, FornecedorEditRequest $storeRequest)
    {
        $storeRequest->merge([
            "uuid" => $uuid
        ]);

        $formData = (new FornecedorCreateAction())->exec();

        $fornecedor = $this->storeAction->exec(FornecedorEditDTO::makeFromRequest($storeRequest));

        return view('app.fornecedor.edit', [
            "fornecedor" => $fornecedor,
            "formData" => $formData
        ]);
    }
}
