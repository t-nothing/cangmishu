<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Console\Commands;
use App\Events\PrivateMessageEvent;
use App\Models\User;
use Illuminate\Console\Command;
class PushMessageByUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:push-message {user} {message}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '推送消息给指定用户';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = User::find($this->argument('user'));
        if (! $user) {
            return $this->error('不存在的用户');
        }
        broadcast(new PrivateMessageEvent(User::find($this->argument('user')), $this->argument('message')));
        $this->info('推送成功');
    }
}