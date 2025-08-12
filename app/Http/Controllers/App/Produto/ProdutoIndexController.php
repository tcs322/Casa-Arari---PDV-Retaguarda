<?php

namespace App\Http\Controllers\App\Produto;

use App\Actions\Produto\ProdutoIndexAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Produto\ProdutoIndexRequest;

class ProdutoIndexController extends Controller
{
    public function __construct(
        protected ProdutoIndexAction $indexAction
    ) { }

    public function index(ProdutoIndexRequest $request)
    {
        $produtos = $this->indexAction->exec(
            page: $request->get('page', 1),
            totalPerPage: $request->get('totalPerPage', 15),
            filter: $request->get('filter', null),
        );

        $filters = ['filter' => $request->get('filter', null)];

        return view('app.produto.index', compact('produtos', 'filters'));
    }
}