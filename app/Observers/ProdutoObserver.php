<?php

namespace App\Observers;

use App\Models\Produto;
use Illuminate\Support\Str;

class ProdutoObserver
{
    public function creating(Produto $produto): void
    {
        $produto->uuid = Str::uuid();
    }

    /**
     * Handle the Produto "created" event.
     */
    public function created(Produto $produto): void
    {
        //
    }

    /**
     * Handle the Produto "updated" event.
     */
    public function updated(Produto $produto): void
    {
        //
    }

    /**
     * Handle the Produto "deleted" event.
     */
    public function deleted(Produto $produto): void
    {
        //
    }

    /**
     * Handle the Produto "restored" event.
     */
    public function restored(Produto $produto): void
    {
        //
    }

    /**
     * Handle the Produto "force deleted" event.
     */
    public function forceDeleted(Produto $produto): void
    {
        //
    }
}
