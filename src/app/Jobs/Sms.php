<?php

namespace App\Jobs;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;

class Sms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * 任务可以尝试的最大次数。
     *
     * @var int
     */
    public $tries = 2;

    /**
     * 任务可以执行的最大秒数 (超时时间)。
     *
     * @var int
     */
    public $timeout = 30;

    var $type;
    var $mobile;
    var $code;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($type, $mobile, $code)
    {
        $this->type = $type;
        $this->mobile = $mobile;
        $this->code = $code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = 'http://v.juhe.cn/sms/send';  // 模板列表
        $params = [
            'key'      => config('juhe.china.sms_key'), //您申请的APPKEY
            'mobile'   => $this->mobile, //接受短信的用户手机号码
            'tpl_id'    => config('juhe.china.register_template_id'), //您申请的短信模板ID，根据实际情况修改
            'tpl_value' =>'#code#='.(string)($this->code) //您设置的模板变量，根据实际情况修改
        ];

        try
        {
            info('开始向'.$this->mobile.'发送短信验证码', $params);

            $client = new Client(['verify' => false]);

            $res = $client->request('get', $url,
                [
                        'query' => $params,
                ]
            );

            info('短信回调成功结果'.$res->getBody());
        } catch(GuzzleException $ex) {
            info('短信回调失败结果'.$ex->getMessage());
        }
    }
}
