<?php

namespace App\Repositories\Nota;

use App\DTO\Nota\NotaStoreDTO;
use App\DTO\Product\ProductStoreDTO;
use App\DTO\Product\ProductUpdateDTO;
use App\Models\Nota;
use App\Models\Product;
use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Presenters\PaginationPresenter;

class NotaEloquentRepository implements NotaRepositoryInterface
{
    public function __construct(
        protected Nota $model
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        $query = $this->model->query()->with(['fornecedor']);

        // if (!is_null($filter)) {
        //     $query->where("nome", "like", "%".$filter."%");
        // }

        if (!is_null($filter)) {
            $query->where(function ($q) use ($filter) {
                $q->where('numero_nota', 'like', '%' . $filter . '%')
                  ->orWhereHas('fornecedor', function ($q) use ($filter) {
                      $q->where('nome_fantasia', 'like', '%' . $filter . '%');
                  });
            });
        }

        $query->orderBy('updated_at', 'desc');

        $result = $query->paginate($totalPerPage, ['*'], 'page', $page);

        return new PaginationPresenter($result);

    }

    public function store(NotaStoreDTO $dto): Nota
    {
        return $this->model->create((array) $dto);
    }

    public function find(string $uuid): Nota
    {
        return $this->model->with('fornecedor')->where("uuid", $uuid)->first();
    }

    // public function update(ProductUpdateDTO $dto): Product
    // {
    //     $this->model->where("uuid", $dto->uuid)->update((array)$dto);

    //     return $this->find($dto->uuid);
    // }
}