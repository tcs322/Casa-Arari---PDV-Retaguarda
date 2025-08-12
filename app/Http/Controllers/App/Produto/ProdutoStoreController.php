<?php

namespace App\Http\Controllers\App\Produto;

use App\Actions\Produto\ProdutoStoreAction;
use App\DTO\Produto\ProdutoStoreDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Produto\ProdutoStoreRequest;

class ProdutoStoreController extends Controller
{
    public function __construct(
        protected ProdutoStoreAction $storeAction
    ) { }

    public function store(ProdutoStoreRequest $request)
    {
        $this->storeAction->exec(ProdutoStoreDTO::makeFromRequest($request));

        return redirect()->route('produto.index');
    }
}