<?php

namespace App\DTO\Cliente;

use App\DTO\BaseDTO;
use App\Http\Requests\App\Cliente\ClienteStoreRequest;

class ClienteStoreDTO extends BaseDTO
{
    public function __construct(
        public string $nome,
        public string $cpf,
        public string $data_nascimento
    ) {}

    public static function makeFromRequest(ClienteStoreRequest $request)
    {
        return new self(
            $request->nome,
            $request->cpf,
            $request->data_nascimento
        );
    }
}