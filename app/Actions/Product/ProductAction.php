<?php

namespace App\Actions\Product;

use App\DTO\Product\ProductEditDTO;
use App\DTO\Product\ProductShowDTO;
use App\DTO\Product\ProductStoreDTO;
use App\DTO\Product\ProductUpdateDTO;
use App\Enums\TipoProducaoProdutoEnum;
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
            'tipo' => TipoProdutoEnum::asArray(),
            'tipo_producao' => TipoProducaoProdutoEnum::asArray(),
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

    public function edit(ProductEditDTO $dto): Product
    {
        return $this->productRepository->find($dto->uuid);
    }

    public function update(ProductUpdateDTO $dto): Product
    {
        return $this->productRepository->update($dto);
    }

    public function show(ProductShowDTO $dto): Product
    {
        return $this->productRepository->find($dto->uuid);
    }
}