<?php

namespace App\Observers;

use App\Models\Cliente;
use Illuminate\Support\Str;

class ClienteObserver
{
    /**
     * Handle the Fornecedor "created" event.
     */
    public function creating(Cliente $cliente): void
    {
        $cliente->uuid = Str::uuid();
    }
}
