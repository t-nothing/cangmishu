<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopSenderAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_sender_address', function (Blueprint $table) {
            $table->increments('id');

            $table->tinyInteger('is_default')->default(1);
            $table->integer('shop_id');
            $table->string('country');
            $table->string('province');
            $table->string('city');
            $table->string('district');
            $table->string('address');
            $table->string('postcode');
            $table->string('fullname');
            $table->string('phone');
            $table->string('email')->default('');
            $table->string('company')->default('');
            $table->string('remark')->default('');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer('deleted_at')->comment('删除时间')->nullable();
            $table->index(['shop_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_sender_address');
    }
}
