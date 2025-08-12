<?php

namespace App\Actions\Usuario;

use App\Repositories\Usuario\UsuarioRepositoryInterface;
use App\Repositories\Interfaces\PaginationInterface;

class UsuarioIndexAction
{
    public function __construct(
        protected UsuarioRepositoryInterface $repository
    ) { }

    public function exec(int $page = 1, int $totalPerPage = 10, string $filter = null): PaginationInterface
    {
        return $this->repository->paginate(page: $page,  totalPerPage: $totalPerPage, filter: $filter);
    }
}
