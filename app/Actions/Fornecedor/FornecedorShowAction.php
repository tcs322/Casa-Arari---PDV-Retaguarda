<?php

namespace App\Actions\Fornecedor;

use App\DTO\Fornecedor\FornecedorShowDTO;
use App\Models\Fornecedor;
use App\Repositories\Fornecedor\FornecedorRepositoryInterface;

class FornecedorShowAction
{
    public function __construct(
        protected FornecedorRepositoryInterface $repository
    ) { }

    public function exec(FornecedorShowDTO $dto): Fornecedor
    {
        return $this->repository->find($dto->uuid);
    }
}
