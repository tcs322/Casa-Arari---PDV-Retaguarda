<?php

namespace App\DTO\Usuario;

use App\DTO\BaseDTO;
use App\Http\Requests\App\Usuario\UsuarioShowRequest;

class UsuarioShowDTO extends BaseDTO
{
    public function __construct(
        public string $uuid
    ){ }

    public static function makeFromRequest(UsuarioShowRequest $request)
    {
        return new self(
            $request->uuid
        );
    }
}
