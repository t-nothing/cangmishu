<?php

namespace App\Listeners;

use App\Events\StockLocationMove;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockLocationMoveNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  StockMove  $event
     * @return void
     */
    public function handle(StockLocationMove $event)
    {
        //
    }
}
