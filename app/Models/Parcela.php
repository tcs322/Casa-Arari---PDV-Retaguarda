<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    use HasFactory;

    protected $table = 'parcela';

    protected $fillable = [
        'uuid',
        'numero',
        'quantidade_total_parcelas',
        'quantidade_real_parcelas',
        'compra_uuid',
        'cliente_uuid',
    ];
}
