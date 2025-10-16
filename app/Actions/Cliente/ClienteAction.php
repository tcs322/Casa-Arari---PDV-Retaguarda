<?php

namespace App\Actions\Cliente;

use App\DTO\Cliente\ClienteStoreDTO;
use App\Models\Cliente;
use App\Repositories\Cliente\ClienteRepositoryInterface;

class ClienteAction
{
    public function __construct(
        protected ClienteRepositoryInterface $repository
    ) {}

    public function store(ClienteStoreDTO $dto): Cliente
    {
        return $this->repository->store($dto);
    }
}