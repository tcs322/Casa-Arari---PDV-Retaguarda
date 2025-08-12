<?php

namespace App\DTO\Usuario;

use App\DTO\BaseDTO;
use App\Http\Requests\App\Usuario\UsuarioEditRequest;

class UsuarioEditDTO extends BaseDTO
{
    public function __construct(
        public string $uuid
    ){ }

    public static function makeFromRequest(UsuarioEditRequest $request)
    {
        return new self(
            $request->uuid
        );
    }
}
