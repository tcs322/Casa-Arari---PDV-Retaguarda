<?php

namespace App\Repositories\Venda;

use App\Models\Venda;
use App\Repositories\Interfaces\PaginationInterface;

interface VendaRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function find(string $uuid): Venda;
}