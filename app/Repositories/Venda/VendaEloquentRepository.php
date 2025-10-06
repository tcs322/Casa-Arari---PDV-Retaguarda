<?php

namespace App\Repositories\Venda;

use App\Models\Venda;
use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Presenters\PaginationPresenter;

class VendaEloquentRepository implements VendaRepositoryInterface
{
    public function __construct(
        protected Venda $model
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        $query = $this->model->query();

        if (!is_null($filter)) {
            $query->where("uuid", "like", "%".$filter."%");
        }

        // if (!is_null($filter)) {
        //     $query->where(function ($q) use ($filter) {
        //         $q->where('nome_titulo', 'like', '%' . $filter . '%')
        //           ->orWhereHas('fornecedor', function ($q) use ($filter) {
        //               $q->where('nome_fantasia', 'like', '%' . $filter . '%');
        //           });
        //     });
        // }

        $query->orderBy('created_at', 'desc');

        $result = $query->paginate($totalPerPage, ['*'], 'page', $page);

        return new PaginationPresenter($result);

    }
}