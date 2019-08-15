<?php

namespace App\Listeners;

use App\Events\StockMove;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockMoveNotification
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
    public function handle(StockMove $event)
    {
        //
    }
}
