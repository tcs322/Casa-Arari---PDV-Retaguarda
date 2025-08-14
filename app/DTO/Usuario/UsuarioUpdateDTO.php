<?php

namespace App\DTO\Usuario;

use App\DTO\BaseDTO;
use App\Enums\SituacaoUsuarioEnum;
use App\Enums\TipoUsuarioEnum;
use App\Http\Requests\App\Usuario\UsuarioUpdateRequest;

class UsuarioUpdateDTO extends BaseDTO
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $email,
        public string $role,
        public string $situacao
    ){ }

    public static function makeFromRequest(UsuarioUpdateRequest $request)
    {
        return new self(
            $request->uuid,
            $request->name,
            $request->email,
            TipoUsuarioEnum::getValue($request->role),
            SituacaoUsuarioEnum::getValue(SituacaoUsuarioEnum::getKey((int)$request->situacao))
        );
    }
}
