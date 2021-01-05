<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_product', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('batch_id')->comment('入库单ID');
            $table->integer('spec_id')->comment('规格ID');
            $table->integer('warehouse_id')->comment('仓库ID');
            $table->integer('distributor_id')->comment('供应商ID');
            $table->integer('need_num')->comment('申请入库数量');
            $table->integer('stockin_num')->comment('入库数量');
            $table->integer('pieces_num')->comment('每箱件数');
            $table->string('remark', 100)->comment('备注');
            $table->string('relevance_code', 100)->comment('货品外部编码');
            $table->string('distributor_code', 100)->comment('供应商贷号');
            $table->integer('status')->comment('1代入库,2 入库成功');
            $table->string('ean', 100)->comment('ean码');
            $table->string('box_code', 100)->comment('箱子编号');
            $table->integer('owner_id')->comment('所属用户');
            $table->integer('warehouse_location_id')->comment('推荐货号');
            $table->index('relevance_code');
            $table->index('ean');
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
        Schema::dropIfExists('batch_product');
    }
}
