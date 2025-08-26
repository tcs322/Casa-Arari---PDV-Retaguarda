<?php

namespace App\Actions\Fornecedor;

use App\Enums\TipoDocumentoPessoaJuridicaEnum;
use App\Enums\TipoFornecedorEnum;

class FornecedorCreateAction
{
    public function __construct(
    ) { }

    public function exec(): array
    {
        return [
            "tipo" => TipoFornecedorEnum::asArray(),
            "tipo_documento" => TipoDocumentoPessoaJuridicaEnum::asArray(),
        ];
    }
}
