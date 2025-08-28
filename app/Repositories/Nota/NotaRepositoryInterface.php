<?php

namespace App\Repositories\Nota;

use App\DTO\Product\ProductStoreDTO;
use App\DTO\Product\ProductUpdateDTO;
use App\Models\Product;
use App\Repositories\Interfaces\PaginationInterface;

interface NotaRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function store(ProductStoreDTO $dto): Product;
    public function find(string $uuid): Product;
    public function update(ProductUpdateDTO $dto): Product;
}