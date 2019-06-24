<?php

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
        ]
        )->onQueue('cangmishu_emails');
    }
}