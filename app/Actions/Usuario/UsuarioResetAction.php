<?php

namespace App\Actions\Usuario;

use App\DTO\Usuario\UsuarioResetDTO;
use App\Models\User;
use App\Repositories\Usuario\UsuarioRepositoryInterface;

class UsuarioResetAction
{
    public function __construct(
        protected UsuarioRepositoryInterface $repository
    ) { }

    public function exec(UsuarioResetDTO $dto): User
    {
        return $this->repository->reset($dto);
    }
}
