<?php

namespace App\DTO\Usuario;

use App\DTO\BaseDTO;
use App\Enums\MustChangePasswordEnum;
use App\Enums\SituacaoUsuarioEnum;
use App\Enums\TipoUsuarioEnum;
use App\Helpers\PasswordHelper;
use App\Http\Requests\App\Usuario\UsuarioStoreRequest;

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
            PasswordHelper::generateTemporaryPassword($randomPassword = false),
            MustChangePasswordEnum::YES()->value,
            SituacaoUsuarioEnum::getValue(SituacaoUsuarioEnum::getKey((int)$request->situacao))
        );
    }
}
