<?php

namespace App\Actions\Fornecedor;

use App\Enums\PortePessoaJuridicaEnum;
use App\Enums\TipoDocumentoPessoaJuridicaEnum;

class FornecedorCreateAction
{
    public function __construct(
    ) { }

    public function exec(): array
    {
        return [
            "porte" => PortePessoaJuridicaEnum::asArray(),
            "tipo_documento" => TipoDocumentoPessoaJuridicaEnum::asArray(),
        ];
    }
}
