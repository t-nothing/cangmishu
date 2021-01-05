<?php

namespace App\Listeners;

use App\Events\CartCheckouting;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CartCheckoutingNotification
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
     * @param  CartCheckouting  $event
     * @return void
     */
    public function handle(CartCheckouting $event)
    {
        //
    }
}
