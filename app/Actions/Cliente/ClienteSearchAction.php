<?php

namespace App\Actions\Cliente;

use App\Repositories\Cliente\ClienteRepositoryInterface;

class ClienteSearchAction
{
    protected $clienteRepository;

    public function __construct(
        ClienteRepositoryInterface $clienteRepository
    )
    {
        $this->clienteRepository = $clienteRepository;
    }

    public function execute(string $search) : array
    {
        return $this->clienteRepository->search($search);
    }
}
