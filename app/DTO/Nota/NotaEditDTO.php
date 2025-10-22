<?php

namespace App\DTO\Nota;

use App\DTO\BaseDTO;
use App\Http\Requests\App\Nota\NotaEditRequest;

class NotaEditDTO extends BaseDTO
{
    public function __construct(
        public string $uuid,
    ) { }

    public static function makeFromRequest(NotaEditRequest $request)
    {
        return new self(
            $request->uuid,
        );
    }
}
