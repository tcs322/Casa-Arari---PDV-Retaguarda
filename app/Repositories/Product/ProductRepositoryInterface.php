<?php

namespace App\Repositories\Product;

use App\DTO\Product\ProductStoreDTO;
use App\Models\Product;
use App\Repositories\Interfaces\PaginationInterface;

interface ProductRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function store(ProductStoreDTO $dto): Product;
}