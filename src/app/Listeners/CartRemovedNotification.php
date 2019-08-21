<?php

namespace App\Listeners;

use App\Events\CartRemoved;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CartRemovedNotification
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
     * @param  CartRemoved  $event
     * @return void
     */
    public function handle(CartRemoved $event)
    {
        //
    }
}
