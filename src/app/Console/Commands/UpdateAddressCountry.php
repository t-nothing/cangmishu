<?php

namespace App\Console\Commands;

use App\Models\ReceiverAddress;
use App\Models\SenderAddress;
use Illuminate\Console\Command;

class UpdateAddressCountry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:update-address-country';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        SenderAddress::query()->where('country', '=', 'CN')
            ->update(['country' => '中国']);
        SenderAddress::query()->where('country', '=', 'HK')
            ->update(['country' => '中国香港']);

        ReceiverAddress::query()->where('country', '=', 'CN')
            ->update(['country' => '中国']);
        ReceiverAddress::query()->where('country', '=', 'HK')
            ->update(['country' => '中国香港']);
    }
}
