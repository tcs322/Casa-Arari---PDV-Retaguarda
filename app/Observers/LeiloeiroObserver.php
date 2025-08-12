<?php

namespace App\Observers;

use App\Models\Leiloeiro;
use Illuminate\Support\Str;

class LeiloeiroObserver
{
    public function creating(Leiloeiro $leiloeiro): void
    {
        $leiloeiro->uuid = Str::uuid();
    }

    /**
     * Handle the Leiloeiro "created" event.
     */
    public function created(Leiloeiro $leiloeiro): void
    {
        $leiloeiro->uuid = Str::uuid();
    }

    /**
     * Handle the Leiloeiro "updated" event.
     */
    public function updated(Leiloeiro $leiloeiro): void
    {
        //
    }

    /**
     * Handle the Leiloeiro "deleted" event.
     */
    public function deleted(Leiloeiro $leiloeiro): void
    {
        //
    }

    /**
     * Handle the Leiloeiro "restored" event.
     */
    public function restored(Leiloeiro $leiloeiro): void
    {
        //
    }

    /**
     * Handle the Leiloeiro "force deleted" event.
     */
    public function forceDeleted(Leiloeiro $leiloeiro): void
    {
        //
    }
}
