<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendaItem extends Model
{
    use HasFactory;

    protected $table = 'venda_items';

    protected $fillable = [
        'uuid',
        'venda_uuid',
        'produto_uuid',
        'quantidade',
        'preco_unitario',
        'preco_total',
        'subtotal',
        'desconto',
        'tipo_desconto'
    ];

    protected $casts = [
        'preco_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'desconto' => 'decimal:2'
    ];

    public function venda()
    {
        return $this->belongsTo(Venda::class, 'venda_uuid', 'uuid');
    }

    public function produto()
    {
        return $this->belongsTo(Product::class, 'produto_uuid', 'uuid');
    }
}