<?php

namespace App\Listeners;

use App\Events\CartAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CartAddedNotification
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
     * @param  CartAdding  $event
     * @return void
     */
    public function handle(CartAdded $event)
    {
        //
    }
}
