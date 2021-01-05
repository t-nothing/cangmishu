<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopWeappFormIdTable extends Migration
{
    /**
     * 小程序要收集formid
     *
     * @return void
     */
    public function up()
    {
         Schema::create('shop_weapp_form_id', function (Blueprint $table) {
            $table->increments('id');
            $table->string('form_id')->comment('form_id');
            $table->tinyInteger('is_used')->comment('状态')->default(0);
            $table->integer('user_id')->comment('用户ID');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');

            $table->index(['user_id','is_used']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_weapp_form_id');
    }
}
