<?php

namespace App\Actions\Venda;

use App\DTO\Venda\VendaShowDTO;
use App\Models\Venda;
use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Venda\VendaEloquentRepository;

class VendaAction
{
    public function __construct(
        protected VendaEloquentRepository $repository
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        return $this->repository->paginate(page: $page, totalPerPage: $totalPerPage, filter: $filter,
        );
    }

    public function show(VendaShowDTO $dto): Venda
    {
        return $this->repository->find($dto->uuid);
    }
}