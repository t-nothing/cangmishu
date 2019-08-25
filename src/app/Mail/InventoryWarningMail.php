<?php
/**
 * Created by PhpStorm.
 * User: lym
 * Date: 2018/5/23
 * Time: 13:26
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InventoryWarningMail extends Mailable implements ShouldQueue
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
    public function __construct($toMail, $name, $stock)
    {
        $this->to($toMail)->with(compact('name', 'stock'));

        app('log')->info('发送邮件 - 库存预警', compact('toMail', 'name', 'stock'));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('仓秘书库存预警提示')->view('emails.inventoryWarningMail');
    }

}
