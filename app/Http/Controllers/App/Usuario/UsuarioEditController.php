<?php

namespace App\Http\Controllers\App\Usuario;

use App\Actions\Usuario\UsuarioCreateAction;
use App\Actions\Usuario\UsuarioEditAction;
use App\DTO\Usuario\UsuarioEditDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Usuario\UsuarioEditRequest;

class UsuarioEditController extends Controller
{
    public function __construct(
        protected UsuarioEditAction $storeAction
    ) { }

    public function Edit(string $uuid, UsuarioEditRequest $storeRequest)
    {
        $storeRequest->merge([
            "uuid" => $uuid
        ]);

        $formData = (new UsuarioCreateAction())->exec();

        $user = $this->storeAction->exec(UsuarioEditDTO::makeFromRequest($storeRequest));

        return view('app.usuario.edit', [
            "user" => $user,
            "formData" => $formData
        ]);
    }
}
