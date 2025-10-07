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
        'tipo_producao',
        'nota_uuid',
        'fornecedor_uuid',
        'ncm',
        'cest',
        'codigo_barras',
        'unidade_medida',
        'aliquota_icms',
        'cst_icms',
        'cst_pis',
        'cst_cofins',
        'cfop',
        'origem',
    ];

    public function fornecedor()
    {
        return $this->hasOne(Fornecedor::class, 'uuid', 'fornecedor_uuid');
    }
}
