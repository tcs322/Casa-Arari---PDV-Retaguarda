<?php

namespace App\Http\Controllers\App\Usuario;

use App\Actions\Usuario\UsuarioCreateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Usuario\UsuarioCreateRequest;
Use App\Models\User;

class UsuarioCreateController extends Controller
{
    public function __construct(
        protected UsuarioCreateAction $createAction
    ) { }

    public function create(UsuarioCreateRequest $usuarioCreateRequest)
    {
        $formData = $this->createAction->exec();

        return view('app.usuario.create', compact('formData'));
    }
}
