<?php

namespace App\Actions\Produto;

use App\Enums\TipoProdutoEnum;

class ProdutoCreateAction
{
    public function __construct() {}  

    public function exec()
    {
        return [
            'tipo' => TipoProdutoEnum::asArray()
        ];
    }
}