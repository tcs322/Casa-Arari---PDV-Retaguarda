<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'codigo',
        'nome_titulo',
        'preco',
        'estoque',
        'autor',
        'edicao',
        'tipo',
        'fornecedor_uuid',
    ];

    public function fornecedor()
    {
        return $this->hasOne(Fornecedor::class, 'uuid', 'fornecedor_uuid');
    }
}
