<?php

namespace App\Listeners;

use App\Events\CartCheckouted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CartCheckoutedNotification
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
     * @param  CartCheckouted  $event
     * @return void
     */
    public function handle(CartCheckouted $event)
    {
        //
    }
}
