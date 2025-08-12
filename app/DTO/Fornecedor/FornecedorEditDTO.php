<?php

namespace App\DTO\Fornecedor;

use App\DTO\BaseDTO;
use App\Http\Requests\App\Fornecedor\FornecedorEditRequest;

class FornecedorEditDTO extends BaseDTO
{
    public function __construct(
        public string $uuid
    ){ }

    public static function makeFromRequest(FornecedorEditRequest $request)
    {
        return new self(
            $request->uuid
        );
    }
}
