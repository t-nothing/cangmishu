<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldToShopProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('shop_product', function (Blueprint $table)  {
            $table->json('pics')->comment('商品图片JSON')->nullable();
            $table->text('remark')->comment('备注')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('shop_product', function (Blueprint $table) {
            $table->dropColumn('pics');
            $table->dropColumn('remark');
        });
    }
}
