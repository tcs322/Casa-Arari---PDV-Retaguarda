<?php

namespace App\Actions\Fornecedor;

use App\DTO\Fornecedor\FornecedorUpdateDTO;
use App\Models\Fornecedor;
use App\Repositories\Fornecedor\FornecedorRepositoryInterface;

class FornecedorUpdateAction
{
    public function __construct(
        protected FornecedorRepositoryInterface $repository
    ) { }

    public function exec(FornecedorUpdateDTO $dto): Fornecedor
    {
        return $this->repository->update($dto);
    }
}
