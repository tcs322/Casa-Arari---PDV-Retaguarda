<?php

namespace App\Http\Controllers\App\Usuario;

use App\Actions\Usuario\UsuarioIndexAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Usuario\UsuarioIndexRequest;

class UsuarioIndexController extends Controller
{
    public function __construct(
        protected UsuarioIndexAction $indexAction
    ) {}

    public function index(UsuarioIndexRequest $usuarioIndexRequest)
    {
        $user = $this->indexAction->exec(
            page: $usuarioIndexRequest->get('page', 1),
            totalPerPage: $usuarioIndexRequest->get('totalPerPage', 15),
            filter: $usuarioIndexRequest->get('filter', null),
        );

        $filters = ['filter' => $usuarioIndexRequest->get('filter', null)];

        return view('app.usuario.index', compact('user', 'filters'));
    }
}
