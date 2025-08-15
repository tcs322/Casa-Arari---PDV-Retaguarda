<?php

namespace App\Http\Controllers\App\Usuario;

use App\Actions\Usuario\UsuarioUpdateAction;
use App\DTO\Usuario\UsuarioUpdateDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Usuario\UsuarioUpdateRequest;

class UsuarioUpdateController extends Controller
{
    public function __construct(
        protected UsuarioUpdateAction $updateAction
    ) {}

    public function update(string $uuid, UsuarioUpdateRequest $updateRequest)
    {
        $updateRequest = $updateRequest->merge([
            'uuid' => $uuid,
        ]);

        dd($updateRequest->all());

        $this->updateAction->exec(UsuarioUpdateDTO::makeFromRequest($updateRequest));

        return redirect()->route('usuario.index')->with('message', 'Registro atualizado');
    }
}
