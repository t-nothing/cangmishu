<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\StockIn' => [
                'App\Listeners\StockInNotification',
                'App\Listeners\StockInLogNotification',
            ],
        'App\Events\StockPutOn' => [
                'App\Listeners\StockPutOnNotification',
                'App\Listeners\StockPutOnLogNotification',
            ],
        'App\Events\StockPick' => [
                'App\Listeners\StockPickNotification',
                'App\Listeners\StockPickLogNotification',
            ],
        'App\Events\StockOut' => [
                'App\Listeners\StockOutNotification',
                'App\Listeners\StockOutLogNotification',
            ],
        'App\Events\StockAdjust' => [
                'App\Listeners\StockAdjustNotification',
                'App\Listeners\StockAdjustLogNotification',
            ],
        'App\Events\StockMove' => [
                'App\Listeners\StockMoveNotification',
                'App\Listeners\StockMoveLogNotification',
            ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

    }
}
