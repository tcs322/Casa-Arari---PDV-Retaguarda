<?php

namespace App\Actions\Fornecedor;

use App\Repositories\Fornecedor\FornecedorRepositoryInterface;
use App\Repositories\Interfaces\PaginationInterface;

class FornecedorIndexAction
{
    public function __construct(
        protected FornecedorRepositoryInterface $repository
    ) { }

    public function exec(int $page = 1, int $totalPerPage = 10, string $filter = null): PaginationInterface
    {
        return $this->repository->paginate(page: $page,  totalPerPage: $totalPerPage, filter: $filter);
    }
}
