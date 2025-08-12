<?php

namespace App\Repositories\Produto;

use App\DTO\Produto\ProdutoStoreDTO;
use App\Models\Produto;
use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Presenters\PaginationPresenter;

class ProdutoEloquentRepository implements ProdutoRepositoryInterface
{
    protected $model;
    
    public function __construct(Produto $model)
    { 
        $this->model = $model;
    }

    public function paginate(int $page = 1, int $totalPerPage = 10, string $filter = null): PaginationInterface
    {
        $query = $this->model->query();

        if(!is_null($filter)) {
            $query->where("nome", "like", "%".$filter."%");
        }

        $query->orderBy('updated_at', 'desc');

        $result = $query->paginate($totalPerPage, ['*'], 'page', $page);

        return new PaginationPresenter($result);
    }

    public function new(ProdutoStoreDTO $dto): Produto
    {
        return $this->model->create((array) $dto);
    }

    public function all()
    {
        return $this->model->all();
    }
}
