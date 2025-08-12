<?php

namespace App\Repositories\Produto;

use App\DTO\Produto\ProdutoStoreDTO;
use App\Models\Produto;
use App\Repositories\Interfaces\PaginationInterface;

interface ProdutoRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 10, string $filter = null): PaginationInterface; 

    public function new(ProdutoStoreDTO $dto): Produto;

    public function all();
}
