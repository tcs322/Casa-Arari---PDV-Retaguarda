<?php

namespace App\Actions\Usuario;

use App\DTO\Usuario\UsuarioUpdateDTO;
use App\Models\User;
use App\Repositories\Usuario\UsuarioRepositoryInterface;

class UsuarioUpdateAction
{
    public function __construct(
        protected UsuarioRepositoryInterface $repository
    ) { }

    public function exec(UsuarioUpdateDTO $dto): User
    {
        return $this->repository->update($dto);
    }
}
