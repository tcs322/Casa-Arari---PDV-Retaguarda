<?php

namespace App\DTO\Produto;

use App\Http\Requests\App\Produto\ProdutoStoreRequest;

class ProdutoStoreDTO
{
    public function __construct(
        public string $nome,
        public string $descricao,
        public float $peso,
        public string $tipo
    ) { }

    public static function makeFromRequest(ProdutoStoreRequest $request): self
    {
        return new self(
            $request->nome,
            $request->descricao,
            $request->peso,
            $request->tipo
        );
    }
}