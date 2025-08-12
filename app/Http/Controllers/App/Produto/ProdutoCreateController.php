<?php

namespace App\Http\Controllers\App\Produto;

use App\Actions\Produto\ProdutoCreateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Produto\ProdutoCreateRequest;

class ProdutoCreateController extends Controller
{
    public function __construct(
        protected ProdutoCreateAction $createAction
    ) { }

    public function create(ProdutoCreateRequest $request)
    {
        $formData = $this->createAction->exec();
        
        return view('app.produto.create', compact('formData'));
    }
}