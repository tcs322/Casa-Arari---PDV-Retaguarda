<?php

namespace App\DTO\Cliente;

use App\DTO\BaseDTO;
use Illuminate\Http\Request;

class ClienteEditDTO extends BaseDTO
{
    public function __construct(
        public string $uuid,
    ) {}

    public static function makeFromRequest(Request $request)
    {
        return new self(
            $request->uuid,
        );
    }
}