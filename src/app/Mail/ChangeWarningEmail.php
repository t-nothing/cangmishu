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

class ChangeWarningEmail extends Mailable implements ShouldQueue
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

    public function __construct($toMail, $name, $wmsUrl,$sendDate,$imageUrl,$typeName,$currentTime,$new_email,$old_email)
    {
        $this->to($toMail)->with(compact('name', 'wmsUrl','sendDate','imageUrl','typeName','currentTime','new_email','old_email'));
    }

    public function build()
    {
        return $this->subject('您的库存不足,仓秘书仓储管理系统预警')->view('emails.changeWarningEmail');
    }
}