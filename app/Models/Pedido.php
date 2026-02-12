<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'cliente_nome',
        'itens',
        'status',
        'status_pagamento',
        'valor_total',
    ];

    protected $casts = [
        'itens' => 'array',
    ];
}
