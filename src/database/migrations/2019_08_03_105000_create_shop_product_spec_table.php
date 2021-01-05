<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopProductSpecTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_product_spec', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id');
            $table->integer('spec_id')->comment('原商品规格ID');
            $table->string('name_cn')->comment('中文规格名');
            $table->string('name_en')->comment('英文规格名');
            $table->integer('shop_product_id')->comment('店铺商品ID');
            $table->integer('product_id')->comment('原商品ID');
            $table->decimal('sale_price',10 , 2)->comment('销售价格');
            $table->tinyInteger("is_shelf")->comment("是否上架")->default(1);
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->unique(['shop_id', 'shop_product_id', 'spec_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_product_spec');
    }
}
