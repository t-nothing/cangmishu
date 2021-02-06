<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Listeners;

use App\Events\AppAccountCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Model\User;
use App\Mail\AppAccountMail as Mailable;
use Mail;

class AppAccountCreatedNotification implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'cangmishu_emails';

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 20;
    /**
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
     * @param  AppAccountCreated  $event
     * @return void
     */
    public function handle(AppAccountCreated $event)
    {
        $apiAccount = $event->account;
        $message = new Mailable($apiAccount->owner_email, $apiAccount->owner_name, $apiAccount->warehouse_name_cn, $apiAccount->api_key, $apiAccount->app_secret);
        Mail::send($message);
    }
}
