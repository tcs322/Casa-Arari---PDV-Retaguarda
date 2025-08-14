<?php

namespace App\Actions\Usuario;

use App\Enums\TipoUsuarioEnum;

class UsuarioCreateAction
{
    public function __construct(
    ) { }

    public function exec(): array
    {
        return [
            'role' => TipoUsuarioEnum::asArray(),
        ];
    }
}
