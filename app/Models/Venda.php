<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venda extends Model
{
    use HasFactory;

    protected $table = 'vendas';

    protected $fillable = [
        'uuid',
        'usuario_uuid',
        'forma_pagamento',
        'bandeira_cartao',
        'quantidade_parcelas',
        'valor_total',
        'valor_recebido',
        'troco',
        'numero_nota_fiscal',
        'status',
        'observacoes',
        'data_venda',
        'chave_acesso_nfe',
        'xml_nfe', 
        'status_nfe',
        'erro_nfe'
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'valor_recebido' => 'decimal:2',
        'troco' => 'decimal:2',
        'quantidade_parcelas' => 'integer',
        'data_venda' => 'datetime'
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(VendaItem::class, 'venda_uuid', 'uuid');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_uuid', 'uuid');
    }

    // Escopos úteis
    public function scopeFinalizadas($query)
    {
        return $query->where('status', 'finalizada');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeCanceladas($query)
    {
        return $query->where('status', 'cancelada');
    }

    public function scopeDoDia($query)
    {
        return $query->whereDate('data_venda', today());
    }

    public function scopeDoPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_venda', [$dataInicio, $dataFim]);
    }

    protected $appends = ['created_at_for_humans'];

    public function getCreatedAtForHumansAttribute()
    {
        return Carbon::createFromTimeStamp(strtotime($this->attributes['created_at']) )->diffForHumans();
    }
}