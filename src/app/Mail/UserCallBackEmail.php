<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserCallBackEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    public $logo;
    public $qrCode;
    public  $url;
    public $name;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    public function __construct($logo,$qrCode,$url,$name)
    {
        $this->logo = $logo;
        $this->qrCode = $qrCode;
        $this->url = $url;
        $this->name = $name;
    }

    public function build()
    {
        return $this->subject('仓秘书 高效、简洁的仓库管理系统')->view('emails.userCallBackMail')->with([
            'logo'=>$this->logo,
            'qrCode'=>$this->qrCode,
            'url' => $this->url,
            'name' => $this->name,
        ]);
    }
}