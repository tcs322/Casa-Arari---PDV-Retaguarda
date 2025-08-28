<?php

namespace App\Actions\Nota;

use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Nota\NotaRepositoryInterface;

class NotaAction{
    
    public function __construct(
        protected NotaRepositoryInterface $notaRepository
    ) {}
    
    
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        return $this->notaRepository->paginate(page: $page, totalPerPage: $totalPerPage, filter: $filter);
    }
}