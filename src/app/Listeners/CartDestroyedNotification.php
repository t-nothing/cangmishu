<?php

namespace App\Listeners;

use App\Events\CartDestroyed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CartDestroyedNotification
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
     * @param  CartDestroyed  $event
     * @return void
     */
    public function handle(CartDestroyed $event)
    {
        //
    }
}
