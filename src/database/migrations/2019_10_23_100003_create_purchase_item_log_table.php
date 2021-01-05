<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class CreatePurchaseItemLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_item_log', function (Blueprint $table)  {     
            $table->increments('id'); 
            $table->integer('purchase_id')->comment('采购单ID')->default(0);
            $table->integer('purchase_item_id')->comment('采购单明细ID')->default(0);
            $table->integer('warehouse_id')->comment('仓库ID');
            $table->integer('need_num')->comment('申请采购数量');
            $table->integer('confirm_num')->comment('实际数量');
            $table->integer('total_confirm_num')->comment('实际数量和');
            $table->date('confirm_date')->comment('最后确认日期');
            $table->integer('owner_id')->comment('所属用户');

            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->nullable()->comment('修改时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');

            $table->index(['purchase_item_id']);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_item_log');
    }
}
