<?php

namespace App\Actions\Cliente;

use App\DTO\Cliente\ClienteEditDTO;
use App\DTO\Cliente\ClienteShowDTO;
use App\DTO\Cliente\ClienteStoreDTO;
use App\DTO\Cliente\ClienteUpdateDTO;
use App\Models\Cliente;
use App\Repositories\Cliente\ClienteRepositoryInterface;
use App\Repositories\Interfaces\PaginationInterface;

class ClienteAction
{
    public function __construct(
        protected ClienteRepositoryInterface $repository
    ) {}

    public function store(ClienteStoreDTO $dto): Cliente
    {
        return $this->repository->store($dto);
    }

    public function index(int $page = 1, int $totalPerPage = 10, string $filter = null): PaginationInterface
    {
        return $this->repository->paginate(page: $page,  totalPerPage: $totalPerPage, filter: $filter);
    }

    public function edit(ClienteEditDTO $dto)
    {   
        return $this->repository->find($dto->uuid);
    }

    public function update(ClienteUpdateDTO $dto): Cliente
    {
        return $this->repository->update($dto);
    }

    public function show(ClienteShowDTO $dto): Cliente
    {
        return $this->repository->find($dto->uuid);
    }
}