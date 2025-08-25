<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    use HasFactory;

    protected $table = "fornecedores";

    protected $fillable = [
        "uuid",
        "razao_social",
        "nome_fantasia",
        "tipo",
        "tipo_documento",
        "documento"
    ];

    protected $appends = ['created_at_for_humans', 'updated_at_for_humans'];

    public function getCreatedAtForHumansAttribute()
    {
        return Carbon::createFromTimeStamp(strtotime($this->attributes['created_at']) )->diffForHumans();
    }

    public function getUpdatedAtForHumansAttribute()
    {
        return Carbon::createFromTimeStamp(strtotime($this->attributes['updated_at']) )->diffForHumans();
    }
}
