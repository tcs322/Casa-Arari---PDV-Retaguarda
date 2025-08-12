<?php

namespace App\Repositories\Usuario;

use App\DTO\Usuario\UsuarioStoreDTO;
use App\DTO\Usuario\UsuarioUpdateDTO;
use App\Models\User;
use App\Repositories\Interfaces\PaginationInterface;

interface UsuarioRepositoryInterface
{
    public function all(array $filters): array;
    public function totalQuantity() : int;
    public function paginate(int $page = 1, int $totalPerPage = 10, string $filter = null) : PaginationInterface;
    public function find(string $uuid): User;
    public function new(UsuarioStoreDTO $usuarioStoreDTO): User;
    public function update(UsuarioUpdateDTO $usuarioUpdateDTO): User;
}
