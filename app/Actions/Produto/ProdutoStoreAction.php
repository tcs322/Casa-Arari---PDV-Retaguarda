<?php

namespace App\Actions\Produto;

use App\DTO\Produto\ProdutoStoreDTO;
use App\Models\Produto;
use App\Repositories\Produto\ProdutoRepositoryInterface;

class ProdutoStoreAction
{
    public function __construct(
        protected ProdutoRepositoryInterface $repository
    ) { }

    public function exec(ProdutoStoreDTO $dto): Produto
    {
        return $this->repository->new($dto);
    }
}