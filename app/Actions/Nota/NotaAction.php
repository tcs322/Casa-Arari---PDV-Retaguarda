<?php

namespace App\Actions\Nota;

use App\DTO\Nota\NotaStoreDTO;
use App\Enums\TipoProdutoEnum;
use App\Models\Nota;
use App\Repositories\Fornecedor\FornecedorRepositoryInterface;
use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Nota\NotaRepositoryInterface;

class NotaAction{
    
    public function __construct(
        protected NotaRepositoryInterface $notaRepository,
        protected FornecedorRepositoryInterface $fornecedorRepository
    ) {}
    
    
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        return $this->notaRepository->paginate(page: $page, totalPerPage: $totalPerPage, filter: $filter);
    }

    public function create(): array
    {
        return [
            'tipo_nota' => TipoProdutoEnum::asArray(),
            'fornecedores' => $this->fornecedorRepository->all(),
        ];
    }

    public function store(NotaStoreDTO $dto): Nota
    {
        return $this->notaRepository->store($dto);
    }

    public function show(string $uuid): Nota
    {
        return $this->notaRepository->find($uuid);
    }
}