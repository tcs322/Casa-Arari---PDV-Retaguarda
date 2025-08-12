<?php

namespace App\DTO\Product;

use Illuminate\Http\Request;

class ProductStoreDTO
{
    public function __construct(
        public string $codigo,
        public string $nome_titulo,
        public string $preco,
        public int $estoque,
        public string $autor,
        public int $edicao,
        public string $fornecedor_uuid
    ) {}

    public static function makeFromRequest(Request $request): self
    {
        return new self(
            $request->codigo,
            $request->nome_titulo,
            $request->preco,
            $request->estoque,
            $request->autor,
            $request->edicao,
            $request->fornecedor_uuid
        );
    }
}
