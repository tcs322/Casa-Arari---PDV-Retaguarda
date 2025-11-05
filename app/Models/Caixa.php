<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    use HasFactory;

    protected $table = 'caixas';

    protected $fillable = [
        'usuario_uuid',
        'data_abertura',
        'data_fechamento',
        'saldo_inicial',
        'saldo_final',
        'observacoes'
    ];

    protected $casts = [
        'data_abertura' => 'datetime',
        'data_fechamento' => 'datetime',
        'saldo_inicial' => 'decimal:2',
        'saldo_final' => 'decimal:2'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_uuid', 'uuid');
    }

    public function estaAberto(): bool
    {
        return $this->data_fechamento === null;
    }
}
