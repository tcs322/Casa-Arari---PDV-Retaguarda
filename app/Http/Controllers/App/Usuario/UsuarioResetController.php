<?php

namespace App\Http\Controllers\App\Usuario;

use App\Actions\Usuario\UsuarioResetAction;
use App\DTO\Usuario\UsuarioResetDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Usuario\UsuarioResetRequest;

class UsuarioResetController extends Controller
{
    public function __construct(
        protected UsuarioResetAction $storeAction
    ) { }

    public function reset(UsuarioResetRequest $usuarioResetRequest)
    {
        $this->storeAction->exec(UsuarioResetDTO::makeFromRequest($usuarioResetRequest));

        return redirect()->route('usuario.index')->with('message', 'Senha resetada');
    }
}
