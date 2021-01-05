<?php

namespace App\Providers;

use App\Events\CategoryEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CategoryChangeListener
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
     * @param  CategoryEvent  $event
     * @return void
     */
    public function handle(CategoryEvent $event)
    {
        //
    }
}
