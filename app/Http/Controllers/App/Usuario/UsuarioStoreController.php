<?php

namespace App\Http\Controllers\App\Usuario;

use App\Actions\Usuario\UsuarioStoreAction;
use App\DTO\Usuario\UsuarioStoreDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Usuario\UsuarioStoreRequest;

class UsuarioStoreController extends Controller
{
    public function __construct(
        protected UsuarioStoreAction $storeAction
    ) { }

    public function store(UsuarioStoreRequest $usuarioStoreRequest)
    {
        $this->storeAction->exec(UsuarioStoreDTO::makeFromRequest($usuarioStoreRequest));

        return redirect()->route('usuario.index')->with('message', 'Usuário criado');
    }
}
