<?php

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
        return $this->subject('EU Techne商家仓库管理系统')->view('emails.changeWarningEmail');
    }
}