<?php

namespace App\Actions\Fornecedor;

use App\DTO\Fornecedor\FornecedorStoreDTO;
use App\Models\Fornecedor;
use App\Repositories\Fornecedor\FornecedorRepositoryInterface;

class FornecedorStoreAction
{
    public function __construct(
        protected FornecedorRepositoryInterface $repository
    ) { }

    public function exec(FornecedorStoreDTO $dto): Fornecedor
    {
        return $this->repository->new($dto);
    }
}
