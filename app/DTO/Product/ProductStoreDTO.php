<?php

namespace App\DTO\Product;

use App\Enums\TipoProdutoEnum;
use App\Http\Requests\App\Product\ProductStoreRequest;

class ProductStoreDTO
{
    public function __construct(
        public string $codigo,
        public string $nome_titulo,
        public string $preco,
        public int $estoque,
        public ?string $autor,
        public ?int $edicao,
        public string $tipo,
        public ?string $nota_uuid,
        public string $fornecedor_uuid
    ) {}

    public static function makeFromRequest(ProductStoreRequest $request): self
    {
        return new self(
            $request->codigo,
            $request->nome_titulo,
            $request->preco,
            $request->estoque,
            $request->autor,
            $request->edicao,
            TipoProdutoEnum::getValue($request->tipo),
            $request->nota_uuid,
            $request->fornecedor_uuid
        );
    }
}
