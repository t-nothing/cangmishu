<?php

namespace App\Listeners;

use App\Events\CartDestroying;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CartDestroyingNotification
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
     * @param  CartDestroying  $event
     * @return void
     */
    public function handle(CartDestroying $event)
    {
        //
    }
}
