<?php

namespace App\Repositories\Fornecedor;

use App\DTO\Fornecedor\FornecedorStoreDTO;
use App\DTO\Fornecedor\FornecedorUpdateDTO;
use App\Models\Fornecedor;
use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Presenters\PaginationPresenter;

class FornecedorEloquentRepository implements FornecedorRepositoryInterface
{
    public function __construct(
        protected Fornecedor $model
    ){ }

    public function all()
    {
        return $this->model->all();
    }

    public function totalQuantity() : int {
        return $this->model->count();
    }

    public function find(string $uuid): Fornecedor
    {
        return $this->model->where("uuid", $uuid)->first();
    }

    public function new(FornecedorStoreDTO $fornecedorStoreDTO): Fornecedor
    {
        return $this->model->create((array)$fornecedorStoreDTO);
    }

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        $query = $this->model->query();

        if(!is_null($filter)) {
            $query->where("razao_social", "like", "%".$filter."%");
            $query->orWhere("nome_fantasia", "like", "%".$filter."%");
            $query->orWhere("porte", "like", "%".$filter."%");
            $query->orWhere("tipo_documento", "like", "%".$filter."%");
            $query->orWhere("documento", "like", "%".$filter."%");
        }

        $query->orderBy('updated_at', 'desc');

        $result = $query->paginate($totalPerPage, ['*'], 'page', $page);

        return new PaginationPresenter($result);
    }

    public function update(FornecedorUpdateDTO $fornecedorUpdateDTO): Fornecedor
    {
        $this->model->where("uuid", $fornecedorUpdateDTO->uuid)->update((array)$fornecedorUpdateDTO);

        return $this->find($fornecedorUpdateDTO->uuid);
    }
}
