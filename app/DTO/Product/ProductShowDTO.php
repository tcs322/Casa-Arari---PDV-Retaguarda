<?php

namespace App\DTO\Product;

use App\DTO\BaseDTO;
use App\Http\Requests\App\Product\ProductShowRequest;

class ProductShowDTO extends BaseDTO
{
    public function __construct(
        public string $uuid
    ){ }

    public static function makeFromRequest(ProductShowRequest $request)
    {
        return new self(
            $request->uuid
        );
    }
}
