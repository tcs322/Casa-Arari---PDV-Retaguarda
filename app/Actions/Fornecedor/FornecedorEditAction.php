<?php

namespace App\Actions\Fornecedor;

use App\DTO\Fornecedor\FornecedorEditDTO;
use App\Models\Fornecedor;
use App\Repositories\Fornecedor\FornecedorRepositoryInterface;

class FornecedorEditAction
{
    public function __construct(
        protected FornecedorRepositoryInterface $repository
    ) { }

    public function exec(FornecedorEditDTO $dto): Fornecedor
    {
        return $this->repository->find($dto->uuid);
    }
}
