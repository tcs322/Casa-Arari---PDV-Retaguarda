<?php

namespace App\DTO\Nota;

use App\DTO\BaseDTO;
use App\Enums\TipoProdutoEnum;
use App\Http\Requests\App\Nota\NotaStoreRequest;

class NotaStoreDTO extends BaseDTO
{
    public function __construct(
        public string $numero_nota,
        public string $valor_total,
        public string $fornecedor_uuid,
        public string $tipo_nota,
    ) { }

    public static function makeFromRequest(NotaStoreRequest $request)
    {
        return new self(
            $request->numero_nota,
            $request->valor_total,
            $request->fornecedor_uuid,
            TipoProdutoEnum::getValue($request->tipo_nota)
        );
    }
}
