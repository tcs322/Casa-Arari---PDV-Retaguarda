<?php

namespace App\Repositories\Fornecedor;

use App\DTO\Fornecedor\FornecedorStoreDTO;
use App\DTO\Fornecedor\FornecedorUpdateDTO;
use App\Models\Fornecedor;
use App\Repositories\Interfaces\PaginationInterface;

interface FornecedorRepositoryInterface
{
    public function all();
    public function totalQuantity() : int;
    public function paginate(int $page = 1, int $totalPerPage = 10, string $filter = null) : PaginationInterface;
    public function find(string $uuid): Fornecedor;
    public function new(FornecedorStoreDTO $fornecedorStoreDTO): Fornecedor;
    public function update(FornecedorUpdateDTO $fornecedorStoreDTO): Fornecedor;
}
