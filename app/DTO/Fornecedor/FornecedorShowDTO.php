<?php

namespace App\DTO\Fornecedor;

use App\DTO\BaseDTO;
use App\Http\Requests\App\Fornecedor\FornecedorShowRequest;

class FornecedorShowDTO extends BaseDTO
{
    public function __construct(
        public string $uuid
    ){ }

    public static function makeFromRequest(FornecedorShowRequest $request)
    {
        return new self(
            $request->uuid
        );
    }
}
