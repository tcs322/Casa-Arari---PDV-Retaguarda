<?php

namespace App\Actions\Product;

use App\DTO\Product\ProductStoreDTO;
use App\Enums\TipoProdutoEnum;
use App\Models\Product;
use App\Repositories\Cliente\ClienteRepositoryInterface;
use App\Repositories\Fornecedor\FornecedorRepositoryInterface;
use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Product\ProductRepositoryInterface;

class ProductAction
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected FornecedorRepositoryInterface $fornecedorRepository,
        protected ClienteRepositoryInterface $clienteRepository
    ) {}

    public function create(): array
    {
        return [
            'fornecedores' => $this->fornecedorRepository->all(),
            'tipo' => TipoProdutoEnum::asArray()
        ];
    }

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        return $this->productRepository->paginate(page: $page, totalPerPage: $totalPerPage, filter: $filter,
        );
    }

    public function store(ProductStoreDTO $dto): Product
    {
        return $this->productRepository->store($dto);
    }
}