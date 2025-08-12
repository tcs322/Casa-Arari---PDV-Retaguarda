<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contato extends Model
{
    use HasFactory;

    protected $table = 'contato';

    protected $fillable = [
        'uuid',
        'descricao',
        'telefone_comercial',
        'telefone_residencial',
        'celular_pessoal',
        'celular_comercial',
        'email',
        'cliente_uuid'
    ];
}
