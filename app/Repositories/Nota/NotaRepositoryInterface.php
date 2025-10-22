<?php

namespace App\Repositories\Nota;

use App\DTO\Nota\NotaStoreDTO;
use App\DTO\Nota\NotaUpdateDTO;
use App\Models\Nota;
use App\Repositories\Interfaces\PaginationInterface;

interface NotaRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function store(NotaStoreDTO $dto): Nota;
    public function find(string $uuid): Nota;
    public function update(NotaUpdateDTO $dto): Nota;
}