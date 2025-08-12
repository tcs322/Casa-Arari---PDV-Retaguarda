<?php

namespace App\Http\Controllers\App\Fornecedor;

use App\Actions\Fornecedor\FornecedorCreateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Fornecedor\FornecedorCreateRequest;

class FornecedorCreateController extends Controller
{
    public function __construct(
        protected FornecedorCreateAction $createAction
    ) {}

    public function create(FornecedorCreateRequest $fornecedorCreateRequest)
    {
        $formData = $this->createAction->exec();

        return view('app.fornecedor.create', compact('formData'));
    }
}
