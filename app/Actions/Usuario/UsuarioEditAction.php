<?php

namespace App\Actions\Usuario;

use App\DTO\Usuario\UsuarioEditDTO;
use App\Models\User;
use App\Repositories\Usuario\UsuarioRepositoryInterface;

class UsuarioEditAction
{
    public function __construct(
        protected UsuarioRepositoryInterface $repository
    ) { }

    public function exec(UsuarioEditDTO $dto): User
    {
        return $this->repository->find($dto->uuid);
    }
}
