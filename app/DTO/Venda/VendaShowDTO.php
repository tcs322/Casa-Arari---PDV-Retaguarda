<?php

namespace App\DTO\Venda;

use App\DTO\BaseDTO;
use App\Http\Requests\App\Venda\VendaShowRequest;

class VendaShowDTO extends BaseDTO
{
    public function __construct(
        public string $uuid
    ){ }

    public static function makeFromRequest(VendaShowRequest $request)
    {
        return new self(
            $request->uuid
        );
    }
}
