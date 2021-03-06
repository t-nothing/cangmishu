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

class ExpirationWarningMail extends Mailable implements ShouldQueue
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
     * @param  string $name 商品名
     * @param  int $stock 出库后剩余库存
     * @return void
     */
    public function __construct($toMail, $name, $expirationDate)
    {
        $this->to($toMail)->with(compact('name', 'expirationDate'));

        app('log')->info('发送邮件 - 保质期预警', compact('toMail', 'name', 'expirationDate'));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('保质期预警')->view('emails.expirationWarningMail');
    }

}
