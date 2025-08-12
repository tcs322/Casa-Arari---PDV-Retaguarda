<?php

namespace App\Actions\Dashboard;

use App\Repositories\Fornecedor\FornecedorRepositoryInterface;
use App\Repositories\Usuario\UsuarioRepositoryInterface;

class DashboardIndexAction
{
    public function __construct(
        protected UsuarioRepositoryInterface    $usuarioRepositoryInterface,
        protected FornecedorRepositoryInterface $fornecedorRepositoryInterface
    ) { }

    public function exec(): array
    {
        return [
            'quantitativos' => [
                'fornecedores' => $this->fornecedorRepositoryInterface->totalQuantity(),
                'usuarios' => $this->usuarioRepositoryInterface->totalQuantity(),
            ]
        ];
    }
}
