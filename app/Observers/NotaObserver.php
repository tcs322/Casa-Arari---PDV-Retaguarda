<?php

namespace App\Observers;

use App\Models\Nota;
use Illuminate\Support\Str;

class NotaObserver
{
    /**
     * Handle the Fornecedor "created" event.
     */
    public function creating(Nota $nota): void
    {
        $nota->uuid = Str::uuid();
    }

    /**
     * Handle the Fornecedor "created" event.
     */
    public function created(Nota $nota): void
    {
        //
    }

    /**
     * Handle the Fornecedor "updated" event.
     */
    public function updated(Nota $nota): void
    {
        //
    }

    /**
     * Handle the Fornecedor "deleted" event.
     */
    public function deleted(Nota $nota): void
    {
        //
    }

    /**
     * Handle the Fornecedor "restored" event.
     */
    public function restored(Nota $nota): void
    {
        //
    }

    /**
     * Handle the Fornecedor "force deleted" event.
     */
    public function forceDeleted(Nota $nota): void
    {
        //
    }
}
