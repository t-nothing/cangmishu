<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Warehouse;
use App\Models\AppAccount;
use App\Events\AppAccountCreated;

class CreateAppAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:warehouse {no}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '开放仓库外部接口权限';

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
        $no = trim($this->argument('no'));
        $warehouse = Warehouse::with('owner')->where('code', $no)->first();
        if(!$warehouse)
        {
            echo "仓库找不到:{$no}".PHP_EOL;
            return false;
        }


        app('log')->info('新增APP KEY');
        app('db')->beginTransaction();
        try{
            $model = new AppAccount;
            $model->remark = "开放API接口";
            $model->warehouse_name_cn = $warehouse->name_cn;
            $model->owner_email = $warehouse->owner->email;
            $model->owner_name = $warehouse->owner->nickname;
            $model->app_key = $warehouse->code;
            $model->app_secret = AppAccount::generateAppSecret($warehouse->id, $model->app_key);
            $model->warehouse_id = $warehouse->id;
            $model->owner_id = $warehouse->owner_id;
            $model->is_enabled_push = 1;
            $model->save();
            app('db')->commit();

            event(new AppAccountCreated($model));
           
            echo "APIkey创建成功:".PHP_EOL;
            echo "APIkey:{$model->app_key}".PHP_EOL;
            echo "APIsecret:{$model->app_secret}".PHP_EOL;
        }catch (\Exception $e){
            app('db')->rollback();
            echo "APIkey创建失败:".$e->getMessage().PHP_EOL;
        }
        

        echo "通知完成".PHP_EOL;
    }
}