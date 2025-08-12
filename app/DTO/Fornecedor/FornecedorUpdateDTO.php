<?php

namespace App\DTO\Fornecedor;

use App\DTO\BaseDTO;
use App\Enums\PortePessoaJuridicaEnum;
use App\Enums\TipoDocumentoPessoaJuridicaEnum;
use App\Http\Requests\App\Fornecedor\FornecedorUpdateRequest;

class FornecedorUpdateDTO extends BaseDTO
{
    public function __construct(
        public string $uuid,
        public string $razao_social,
        public string $nome_fantasia,
        public string $documento,
        public string $porte,
        public string $tipo_documento
    ){ }

    public static function makeFromRequest(FornecedorUpdateRequest $request)
    {
        return new self(
            $request->uuid,
            $request->razao_social,
            $request->nome_fantasia,
            $request->documento,
            PortePessoaJuridicaEnum::getValue($request->porte),
            TipoDocumentoPessoaJuridicaEnum::getValue($request->tipo_documento)
        );
    }
}
