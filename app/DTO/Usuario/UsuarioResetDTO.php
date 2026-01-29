<?php

namespace App\DTO\Usuario;

use App\DTO\BaseDTO;
use App\Http\Requests\App\Usuario\UsuarioResetRequest;

class UsuarioResetDTO extends BaseDTO
{
    public function __construct(
        public string $uuid
    ){ }

    public static function makeFromRequest(UsuarioResetRequest $request)
    {
        return new self(
            $request->uuid
        );
    }
}
