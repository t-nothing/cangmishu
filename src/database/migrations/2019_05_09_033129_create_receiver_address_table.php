<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceiverAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receiver_address', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fullname')->comment("姓名");
            $table->string('phone')->comment("电话");
            $table->string('country')->comment("国家");
            $table->string('province')->comment("省份");
            $table->string('city')->comment("城市");
            $table->string('district')->comment("区");
            $table->string('postcode')->comment("邮编");
            $table->string('address')->comment("地址");
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
            $table->integer('owner_id')->comment('所属用户');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receiver_address', function (Blueprint $table) {
            //
        });
    }
}
