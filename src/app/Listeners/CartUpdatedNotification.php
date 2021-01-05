<?php

namespace App\Listeners;

use App\Events\CartUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CartUpdatedNotification
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
     * @param  CartUpdated  $event
     * @return void
     */
    public function handle(CartUpdated $event)
    {
        //
    }
}
