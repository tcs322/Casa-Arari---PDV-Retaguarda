<?php

namespace App\DTO\Product;

use App\Enums\TipoProdutoEnum;
use App\Http\Requests\App\Product\ProductUpdateRequest;

class ProductUpdateDTO
{
    public function __construct(
        public string $uuid,
        public string $codigo,
        public string $nome_titulo,
        public ?string $preco_compra = null,
        public ?string $preco_venda = null,
        public int $estoque,
        public ?string $autor,
        public ?int $edicao,
        public string $tipo,
        public ?string $nota_uuid,
        public string $fornecedor_uuid
    ) {}

    public static function makeFromRequest(ProductUpdateRequest $request): self
    {
        return new self(
            $request->uuid,
            $request->codigo,
            $request->nome_titulo,
            $request->preco_compra,
            $request->preco_venda,
            $request->estoque,
            $request->autor,
            $request->edicao,
            TipoProdutoEnum::getValue($request->tipo),
            $request->nota_uuid,
            $request->fornecedor_uuid
        );
    }
}
