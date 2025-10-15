<?php

namespace App\DTO\Product;

use App\Enums\TipoProducaoProdutoEnum;
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
        public ?string $tipo_producao,
        public ?string $nota_uuid,
        public ?string $fornecedor_uuid,
        // Campos fiscais opcionais para sobrescrever automático
        public ?string $ncm = null,
        public ?string $cest = null,
        public ?string $codigo_barras = null,
        public ?string $unidade_medida = null,
        public ?float $aliquota_icms = null,
        public ?string $cst_icms = null,
        public ?string $cst_pis = null,
        public ?string $cst_cofins = null,
        public ?string $cfop = null,
        public ?string $origem = null
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
            $request->tipo_producao ? TipoProducaoProdutoEnum::getValue($request->tipo_producao) : null,
            $request->nota_uuid,
            $request->fornecedor_uuid,
            // Campos fiscais podem vir do request se fornecidos
            $request->ncm,
            $request->cest,
            $request->codigo_barras,
            $request->unidade_medida,
            $request->aliquota_icms,
            $request->cst_icms,
            $request->cst_pis,
            $request->cst_cofins,
            $request->cfop,
            $request->origem
        );
    }
}