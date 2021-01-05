<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopPaymentMethodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_payment_mentod', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('shop_id')->comment('店铺ID');
            $table->integer('type_id')->comment('支付类型');
            $table->string('qr_code')->comment('二维码');
            $table->string('account_name')->comment('帐户名');
            $table->string('account_number')->comment('帐户号')->nullable();
            $table->string('brand_name')->comment("银行名称")->nullable();
            $table->string('remark')->comment('备注')->nullable();
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer('deleted_at')->comment('删除时间')->nullable();
            $table->unique(['shop_id', 'type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_payment_mentod');
    }
}
