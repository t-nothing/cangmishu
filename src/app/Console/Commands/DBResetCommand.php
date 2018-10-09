<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DBResetCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'db:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset database to init for testing';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {
            DB::table('batch')->truncate();
            DB::table('batch_log')->truncate();
            DB::table('batch_log')->truncate();
            DB::table('kep')->update(['shipment_num' => '']);
            DB::table('order')->truncate();
            DB::table('order_item')->truncate();
            DB::table('product_pick')->truncate();
            DB::table('product_sku')->truncate();
            DB::table('product_stock')->truncate();
            DB::table('product_stock_log')->truncate();
            DB::table('tray')->update([
                'shelf_id' => 0,
                'plies'    => 0,
                'place'    => 0,
                'status'   => 1,
            ]);
        });

        $this->info('Reset DB successfully');
    }
}
