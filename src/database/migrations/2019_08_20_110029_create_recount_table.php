<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('recount', function (Blueprint $table) {
            $table->increments('id');

            $table->string('recount_no')->comment('盘点单号');
            $table->tinyInteger('status')->comment('状态')->nullable();
            $table->string('remark')->comment('盘点备注');
            $table->integer('warehouse_id')->comment('仓库ID');
            $table->integer('owner_id')->comment('添加用户');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer('deleted_at')->comment('删除时间')->nullable();

            $table->index(['warehouse_id', 'owner_id']);
        });

        Schema::create('recount_stock', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('recount_id')->comment('盘点单ID');
            $table->string('sku_product_name_cn')->comment('中文商品规格名称');
            $table->string('sku_product_name_en')->comment('英文商品规格名称');
            $table->string('sku')->comment('SKU');
            $table->string('stock_sku')->comment('入库批次号');
            $table->integer('shelf_num_orgin')->comment('原库存');
            $table->integer('shelf_num_now')->comment('新库存');
            $table->decimal('total_purcharse_orgin', 15, 2)->comment('盘点前金额');
            $table->decimal('total_purcharse_now', 15, 2)->comment('盘点后金额');


            $table->tinyInteger('status')->comment('状态')->nullable();

            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer('deleted_at')->comment('删除时间')->nullable();

             $table->index('recount_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recount');
        Schema::dropIfExists('recount_stock');
    }
}
