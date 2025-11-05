<?php

namespace App\Actions\Dashboard;

use App\Models\Venda;
use App\Repositories\Fornecedor\FornecedorRepositoryInterface;
use App\Repositories\Usuario\UsuarioRepositoryInterface;
use Carbon\Carbon;

class DashboardIndexAction
{
    public function __construct(
        protected UsuarioRepositoryInterface    $usuarioRepositoryInterface,
        protected FornecedorRepositoryInterface $fornecedorRepositoryInterface
    ) { }

    public function exec(): array
    {
        $hoje = Carbon::today();
        $totalDiario = Venda::whereDate('created_at', $hoje)
            ->whereIn('status', ['finalizada', 'pendente'])
            ->sum('valor_total');

        return [
            'quantitativos' => [
                'fornecedores' => $this->fornecedorRepositoryInterface->totalQuantity(),
                'usuarios' => $this->usuarioRepositoryInterface->totalQuantity(),
            ],
            'totalDiario' => $totalDiario
        ];
    }
}
