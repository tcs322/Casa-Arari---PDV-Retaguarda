<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory;

    protected $table = 'vendas';

    protected $fillable = [
        'uuid',
        'usuario_uuid',
        'forma_pagamento',
        'bandeira_cartao',
        'valor_total'
    ];
}
