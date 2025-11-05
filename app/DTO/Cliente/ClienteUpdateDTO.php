<?php

namespace App\DTO\Cliente;

use App\DTO\BaseDTO;
use App\Http\Requests\App\Cliente\ClienteUpdateRequest;

class ClienteUpdateDTO extends BaseDTO
{
    public function __construct(
        public string $uuid,
        public string $nome,
        public string $cpf,
        public string $telefone,
        public string $data_nascimento
    ) {}

    public static function makeFromRequest(ClienteUpdateRequest $request)
    {
        return new self(
            $request->uuid,
            $request->nome,
            $request->cpf,
            $request->telefone,
            $request->data_nascimento
        );
    }
}