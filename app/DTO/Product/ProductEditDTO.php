<?php

namespace App\DTO\Product;

use App\DTO\BaseDTO;
use App\Http\Requests\App\Product\ProductEditRequest;

class ProductEditDTO extends BaseDTO
{
    public function __construct(
        public string $uuid
    ){ }

    public static function makeFromRequest(ProductEditRequest $request)
    {
        return new self(
            $request->uuid
        );
    }
}
