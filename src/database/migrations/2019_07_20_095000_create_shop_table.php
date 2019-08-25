<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('warehouse_id')->comment('仓库ID');
            $table->integer('owner_id')->comment('所用者ID');
            $table->string('domain', 50)->comment('域名');
            $table->string('name_cn', 150)->comment('店铺中文名称');
            $table->string('name_en', 150)->comment('店铺英文名称')->nullable();
            $table->string('logo')->comment('LOGO')->default('');
            $table->string('banner_background')->comment('banner背景图')->default('');
            $table->string('announcement_cn')->comment("英文公告")->default('');
            $table->string('announcement_en')->comment("英文公告")->default('');
            $table->string('pay_notice_cn')->comment("支付说明中文")->default('');
            $table->string('pay_notice_en')->comment("支付说明英文")->default('');
            $table->string('cart_notice_cn')->comment("购物车说明中文")->default('');
            $table->string('cart_notice_en')->comment("购物车说明英文")->default('');
            $table->string('default_lang')->comment('默认语言')->default('zh-cn');
            $table->string('default_currency')->comment('默认货币')->default('CNY');
            $table->tinyInteger("is_closed")->comment("是否关店")->default(0);
            $table->tinyInteger("is_stock_show")->comment("开启实时库存")->default(0);
            $table->tinyInteger("is_price_show")->comment("开启价格显示")->default(1);
            $table->tinyInteger("is_allow_over_order")->comment("是否允许超卖")->default(1);
            $table->string('email')->comment("下单通知EMAIL")->default('');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer('deleted_at')->comment('删除时间')->nullable();
            $table->index('warehouse_id');
            $table->index('owner_id');
            $table->unique('domain');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop');
    }
}
