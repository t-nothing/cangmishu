<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/23
 * Time: 16:37
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HomePageCommand extends Command
{
    protected $signature = 'HomePageCommand {action} {datetime}';

    protected $description = '首页数据更新,action={batchUpdate/orderUpdate/}';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $arg = $this->argument('action');
        switch ($arg) {
            case 'batchUpdate':
                $datetime = $this->argument('datetime');
                //$appSecret = $this->argument('appSecret');
                $this->batchUpdate($datetime);
                break;
            case 'orderUpdate':
                $datetime = $this->argument('datetime');
                //$appSecret = $this->argument('appSecret');
                $this->orderUpdate($datetime);
                break;
            case 'stockUpdate':
                $datetime = $this->argument('datetime');
                //$appSecret = $this->argument('appSecret');
                $this->stockUpdate($datetime);
                break;
        }
    }


    private function batchUpdate($datetime)
    {
        App('BatchStatisticsService')->store($datetime);
        echo "BatchStatisticsService执行完成".PHP_EOL;
    }

    private function orderUpdate($datetime)
    {
        App('OrderStatisticsService')->store($datetime);
        echo "OrderStatisticsService执行完成".PHP_EOL;
    }

    private function stockUpdate($datetime)
    {
        App('StockStatisticsService')->store($datetime);
        echo "StockStatisticsService执行完成".PHP_EOL;
    }
}