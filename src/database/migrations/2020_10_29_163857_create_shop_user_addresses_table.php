<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopUserAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_user_addresses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('shop_user_id')->comment('用户ID');
            $table->string('name')->comment('联系人');
            $table->string('phone')->comment('电话');
            $table->string('address')->comment('详细地址');
            $table->unsignedTinyInteger('is_default')
                ->default(0)
                ->comment('是否默认地址');
            $table->timestamps();
            $table->index(['shop_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_user_addresses');
    }
}
