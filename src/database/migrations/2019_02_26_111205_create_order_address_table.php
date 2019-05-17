<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_address', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->comment("订单id");
            $table->string('express_code')->comment("快递方式");
            $table->string('cn_receiver_fullname')->comment("国内收件人姓名");
            $table->string('cn_receiver_phone')->comment("国内收件人电话");
            $table->string('cn_receiver_country')->comment("国内收件人国家CN");
            $table->string('cn_receiver_province')->comment("国内收件人省份");
            $table->string('cn_receiver_city')->comment("国内收件人城市");
            $table->string('cn_receiver_postcode')->comment("国内收件人邮编");
            $table->string('cn_receiver_address')->comment("国内收件人地址");
            $table->string('cn_receiver_id')->comment("国内收件人身份证号");

            $table->string('cn_send_fullname')->comment("代发代购发件姓名");
            $table->string('cn_send_country')->comment("代发代购发件国家");
            $table->string('cn_send_city')->comment("代发代购发件城市");
            $table->string('cn_send_phone')->comment("代发代购发件电话");
            $table->string('cn_send_postcode')->comment("代发代购发件邮编");
            $table->string('cn_send_address')->comment("代发代购发件地址");
            $table->string('cn_send_doorno')->comment("代发代购发件门牌号");
            $table->string('cn_send_province')->comment("代发代购发件省份");

            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_address');
    }
}
