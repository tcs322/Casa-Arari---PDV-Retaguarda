<?php

namespace App\Actions\Usuario;

use App\DTO\Usuario\UsuarioStoreDTO;
use App\Models\User;
use App\Repositories\Usuario\UsuarioRepositoryInterface;

class UsuarioStoreAction
{
    public function __construct(
        protected UsuarioRepositoryInterface $repository
    ) { }

    public function exec(UsuarioStoreDTO $dto): User
    {
        return $this->repository->new($dto);
    }
}
