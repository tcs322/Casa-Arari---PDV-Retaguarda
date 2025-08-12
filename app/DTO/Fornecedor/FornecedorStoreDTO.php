<?php

namespace App\DTO\Fornecedor;

use App\DTO\BaseDTO;
use App\Enums\PortePessoaJuridicaEnum;
use App\Enums\TipoDocumentoPessoaJuridicaEnum;
use App\Http\Requests\App\Fornecedor\FornecedorStoreRequest;

class FornecedorStoreDTO extends BaseDTO
{
    public function __construct(
        public string $razao_social,
        public string $nome_fantasia,
        public string $documento,
        public string $porte,
        public string $tipo_documento
    ){ }

    public static function makeFromRequest(FornecedorStoreRequest $request)
    {
        return new self(
            $request->razao_social,
            $request->nome_fantasia,
            $request->documento,
            PortePessoaJuridicaEnum::getValue($request->porte),
            TipoDocumentoPessoaJuridicaEnum::getValue($request->tipo_documento)
        );
    }
}
