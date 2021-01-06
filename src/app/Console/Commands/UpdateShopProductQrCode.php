<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class UpdateShopProductQrCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:update-shop-product-qrcode';

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
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public function handle()
    {
        $shops = Shop::all();
        $app = app('wechat.mini_program');

        $this->info("总共需要处理商品数:" . ShopProduct::query()->count());

        ShopProduct::query()->chunkById(50, function (Collection $items) use ($shops, $app) {
            $items->each(function ($shopProduct) use ($shops, $app) {
                $shop = $shops->firstWhere('id', '=', $shopProduct->shop_id);

                if ($shop) {
                    $filePath = storage_path('/app/public/weapp/');

                    $response = $app->app_code->get('pages/index/commodity_details/commodity_details?shop='.$shop->id.'&id='.$shopProduct->id);

                    if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                        $filename = $response->saveAs($filePath, sprintf("%s-%s.png", $shop->domain, $shopProduct->id));

                        $url = Storage::url('weapp/'.$filename);
                        $shopProduct->weapp_qrcode = app('url')->to($url);
                        $shopProduct->save();

                        $this->info("{$shopProduct->id} 商品处理完成".PHP_EOL);
                    } else {
                        $this->info("{$shopProduct->id} 商品处理失败".PHP_EOL);
                    }
                }
            });
        });

        return 0;
    }
}
