<?php

namespace App\DTO\Usuario;

use App\DTO\BaseDTO;
use App\Enums\SituacaoUsuarioEnum;
use App\Enums\TipoUsuarioEnum;
use App\Http\Requests\App\Usuario\UsuarioStoreRequest;
use Illuminate\Support\Facades\Hash;

class UsuarioStoreDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $role,
        public string $password,
        public bool $must_change_password,
        public string $situacao
    ) { }

    public static function makeFromRequest(UsuarioStoreRequest $request)
    {
        return new self(
            $request->name,
            $request->email,
            TipoUsuarioEnum::getValue($request->role),
            Hash::make('123456'),
            true,
            SituacaoUsuarioEnum::getValue(SituacaoUsuarioEnum::getKey((int)$request->situacao))
        );
    }
}
