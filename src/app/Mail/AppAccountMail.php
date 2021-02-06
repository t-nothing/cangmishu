<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AppAccountMail extends Mailable 
{
    use Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * @param  string $toMail 收件邮箱
     * @return void
     */
    public function __construct($toMail, $nick_name, $warehouse_name, $app_key,  $app_secret)
    {

        $logo   =   env("APP_URL")."/images/logo.png";
        $qrCode =   env("APP_URL")."/images/qrCode.png";

        $this->to($toMail)->with(compact('nick_name',  'warehouse_name', 'app_key', 'app_secret', 'logo', 'qrCode'));

        app('log')->info('发送邮件 - 发送api key', compact('toMail', 'nick_name', 'warehouse_name', 'app_key', 'app_sercet', 'logo', 'qrCode'));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('仓秘书仓库外部调用API KEY申请成功')->view('emails.inventoryWarningMail');
    }

}
