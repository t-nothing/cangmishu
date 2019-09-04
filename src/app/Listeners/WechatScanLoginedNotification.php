<?php

namespace App\Listeners;

use App\Events\WechatScanLogined;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class WechatScanLoginedNotification
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
     * @param  WechatScanLogined  $event
     * @return void
     */
    public function handle(WechatScanLogined $event)
    {
        //
    }
}
