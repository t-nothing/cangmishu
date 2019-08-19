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
        'App\Events\StockLocationIn' => [
                'App\Listeners\StockLocationInNotification',
                'App\Listeners\StockLocationInLogNotification',
            ],
        'App\Events\StockLocationPutOn' => [
                'App\Listeners\StockLocationPutOnNotification',
                'App\Listeners\StockLocationPutOnLogNotification',
            ],
        'App\Events\StockLocationPick' => [
                'App\Listeners\StockLocationPickNotification',
                'App\Listeners\StockLocationPickLogNotification',
            ],
        'App\Events\StockLocationOut' => [
                'App\Listeners\StockLocationOutNotification',
                'App\Listeners\StockLocationOutLogNotification',
            ],
        'App\Events\StockLocationAdjust' => [
                'App\Listeners\StockLocationAdjustNotification',
                'App\Listeners\StockLocationAdjustLogNotification',
            ],
        'App\Events\StockLocationMove' => [
                'App\Listeners\StockLocationMoveNotification',
                'App\Listeners\StockLocationMoveLogNotification',
            ],
        'App\Events\OrderCreated' => [ //更新了快递单号
                'App\Listeners\OrderCreatedNotification',
            ],
        'App\Events\OrderPaid' => [ //更新了快递单号
                'App\Listeners\OrderPaidNotification',
            ],
        'App\Events\OrderShipped' => [ //更新了快递单号
                'App\Listeners\OrderShippedNotification',
            ],
        'App\Events\OrderCompleted' => [ //更新了快递单号
                'App\Listeners\OrderCompletedNotification',
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
