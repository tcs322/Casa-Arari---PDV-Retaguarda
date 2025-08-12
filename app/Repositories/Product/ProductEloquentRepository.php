<?php

namespace App\Repositories\Product;

use App\DTO\Product\ProductStoreDTO;
use App\Models\Product;
use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Presenters\PaginationPresenter;

class ProductEloquentRepository implements ProductRepositoryInterface
{
    public function __construct(
        protected Product $model
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        $query = $this->model->query()->with(['fornecedor']);

        // if (!is_null($filter)) {
        //     $query->where("nome", "like", "%".$filter."%");
        // }

        if (!is_null($filter)) {
            $query->where(function ($q) use ($filter) {
                $q->where('nome_titulo', 'like', '%' . $filter . '%')
                  ->orWhereHas('fornecedor', function ($q) use ($filter) {
                      $q->where('nome_fantasia', 'like', '%' . $filter . '%');
                  });
            });
        }

        $query->orderBy('updated_at', 'desc');

        $result = $query->paginate($totalPerPage, ['*'], 'page', $page);

        return new PaginationPresenter($result);

    }

    public function store(ProductStoreDTO $dto): Product
    {
        return $this->model->create((array) $dto);
    }
}