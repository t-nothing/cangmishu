<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductStockLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_stock_location', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stock_id')->comment('库存ID');
            $table->integer('spec_id')->comment('规格ID');
            $table->integer('warehouse_location_id')->comment('仓库货位ID');
            $table->string('sku', 100)->comment('SKU');
            $table->string('ean', 100)->comment('EAN');
            $table->string('relevance_code', 100)->comment('货品外部编码');
            $table->integer('shelf_num')->comment('库存');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer('owner_id')->comment('所属用户');
            $table->integer('warehouse_id')->comment('创建ID');
            $table->integer('sort_num')->comment('拣货时排序值');
            $table->index('relevance_code');
            $table->index('sku');
            $table->index('warehouse_location_id');
            $table->index('stock_id');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_stock_location');
    }
}
