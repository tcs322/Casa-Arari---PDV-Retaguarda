<?php

namespace App\Actions\Produto;

use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Produto\ProdutoRepositoryInterface;

class ProdutoIndexAction
{
    public function __construct(
        protected ProdutoRepositoryInterface $repository
    ) { }

    public function exec(int $page = 1, int $totalPerPage = 10, string $filter = null): PaginationInterface
    {
        return $this->repository->paginate($page, $totalPerPage, $filter);
    }
}
