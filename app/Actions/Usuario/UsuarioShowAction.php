<?php

namespace App\Actions\Usuario;

use App\DTO\Usuario\UsuarioShowDTO;
use App\Models\User;
use App\Repositories\Usuario\UsuarioRepositoryInterface;

class UsuarioShowAction
{
    public function __construct(
        protected UsuarioRepositoryInterface $repository
    ) { }

    public function exec(UsuarioShowDTO $dto): User
    {
        return $this->repository->find($dto->uuid);
    }
}
