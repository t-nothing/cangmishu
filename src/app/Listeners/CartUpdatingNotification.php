<?php

namespace App\Listeners;

use App\Events\CartUpdating;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CartUpdatingNotification
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
     * @param  CartUpdating  $event
     * @return void
     */
    public function handle(CartUpdating $event)
    {
        //
    }
}
