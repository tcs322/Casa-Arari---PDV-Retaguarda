<?php

namespace App\Observers;

use App\Models\Fornecedor;
use Illuminate\Support\Str;

class FornecedorObserver
{
    /**
     * Handle the Fornecedor "created" event.
     */
    public function creating(Fornecedor $fornecedor): void
    {
        $fornecedor->uuid = Str::uuid();
    }

    /**
     * Handle the Fornecedor "created" event.
     */
    public function created(Fornecedor $fornecedor): void
    {
        //
    }

    /**
     * Handle the Fornecedor "updated" event.
     */
    public function updated(Fornecedor $fornecedor): void
    {
        //
    }

    /**
     * Handle the Fornecedor "deleted" event.
     */
    public function deleted(Fornecedor $fornecedor): void
    {
        //
    }

    /**
     * Handle the Fornecedor "restored" event.
     */
    public function restored(Fornecedor $fornecedor): void
    {
        //
    }

    /**
     * Handle the Fornecedor "force deleted" event.
     */
    public function forceDeleted(Fornecedor $fornecedor): void
    {
        //
    }
}
