<?php

namespace App\Repositories\Cliente;

use App\Models\Cliente;
use App\Repositories\Interfaces\PaginationInterface;

interface ClienteRepositoryInterface
{
    public function all();

    public function totalQuantity() : int;

    public function find(string $uuid): Cliente;

    public function paginate(int $page = 1, int $totalPerPage = 10, string $filter = null): PaginationInterface;

    public function search(string $search): array;
}
