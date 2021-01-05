<?php

namespace App\Listeners;

use App\Events\CartRemoving;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CartRemovingNotification
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
     * @param  CartRemoving  $event
     * @return void
     */
    public function handle(CartRemoving $event)
    {
        //
    }
}
