<?php

namespace App\Listeners;

use App\Events\CartAdding;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CartAddingNotification
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
    public function handle(CartAdding $event)
    {
        //
    }
}
