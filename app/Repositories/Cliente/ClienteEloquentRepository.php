<?php

namespace App\Repositories\Cliente;

use App\DTO\Cliente\ClienteStoreDTO;
use App\Models\Cliente;
use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Presenters\PaginationPresenter;

class ClienteEloquentRepository implements ClienteRepositoryInterface
{
    protected $model;

    public function __construct(Cliente $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function totalQuantity() : int {
        return $this->model->count();
    }

    public function find(string $uuid): Cliente
    {
        return $this->model
            ->where('uuid', $uuid)->firstOrFail();
    }

    public function paginate(int $page = 1, int $totalPerPage = 10, string $filter = null): PaginationInterface
    {
        $query = $this->model->query();

        if(!is_null($filter)) {
            $query->where("nome", "like", "%".$filter."%");
            $query->orWhere("cpf_cnpj", "like", "%".$filter."%");
        }

        $query->orderBy('updated_at', 'desc');

        $result = $query->paginate($totalPerPage, ['*'], 'page', $page);

        return new PaginationPresenter($result);
    }

    public function search(string $search): array
    {
        $query = $this->model->query();

        if(!is_null($search)) {
            $query->where("nome", "like", "%".$search."%");
            $query->orWhere("cpf_cnpj", "like", "%".$search."%");
            $query->orWhere("email", "like", "%".$search."%");
            $query->orWhere("endereco", "like", "%".$search."%");
            $query->orWhere("cep", "like", "%".$search."%");
            $query->orWhere("uf", "like", "%".$search."%");
            $query->orWhere("cidade", "like", "%".$search."%");
            $query->orWhere("complemento", "like", "%".$search."%");
        }

        $query->orderBy('updated_at', 'desc')->get()->toArray();

        return $query->get()->toArray();
    }

    public function store(ClienteStoreDTO $dto): Cliente
    {
        return $this->model->create((array) $dto);
    }
}
