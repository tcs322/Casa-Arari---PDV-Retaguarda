<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'uuid',
        'nome',
        'cpf',
        'telefone',
        'data_nascimento',
    ];

    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class, 'cliente_uuid', 'uuid');
    }

    public function contarVendas(): int
    {
        return $this->vendas()
            ->where('status', '!=', 'cancelada')
            ->count();
    }
}
