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

class VerifyCodeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    public $code;
    public $logo;
    public $qrCode;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    public function __construct($code,$logo,$qrCode)
    {
        $this->code = $code;
        $this->logo = $logo;
        $this->qrCode = $qrCode;
    }

    public function build()
    {
        return $this->subject('仓秘书 商家仓库管理系统')->view('emails.verifyCodeMail')->with([
            'code'=>$this->code,
            'logo'=>$this->logo,
            'qrCode'=>$this->qrCode,
        ]);
    }
}