<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_info', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->string('app_key', '32')->comment('APP KEY');
            $table->string('app_secret', '64')->comment('APP密匙');
            $table->string('remark', '255')->nullable()->comment('备注');
            $table->string('name', '50')->nullable()->comment('APP名字');
            $table->integer('bind_user_id')->nullable()->comment('绑定用户');
            $table->integer('default_warning_stock')->nullable()->comment('默认预警库存');
            $table->string('warning_email', '255')->nullable()->comment('预警发送邮箱');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_info');
    }
}
