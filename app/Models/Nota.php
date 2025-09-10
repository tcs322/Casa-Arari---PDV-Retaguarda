<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    use HasFactory;

    protected $table = "notas";

    protected $fillable = [
        'numero_nota',
        'valor_total',
        'fornecedor_uuid',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_uuid', 'uuid');
    }

    protected $appends = ['created_at_for_humans'];

    public function getCreatedAtForHumansAttribute()
    {
        return Carbon::createFromTimeStamp(strtotime($this->attributes['created_at']) )->diffForHumans();
    }
}
