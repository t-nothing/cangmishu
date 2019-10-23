<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class CreatePurchaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase', function (Blueprint $table)  {     
            $table->increments('id'); 
            $table->string('purchase_code')->comment('采购单号');
            $table->string('order_invoice_number')->comment('订单发票号');
            $table->string('created_date')->comment('采购日期');
            $table->string('remark')->comment('采购备注');
            $table->unsignedInteger('distributor_id')->comment('供应商ID');
            $table->unsignedInteger('owner_id')->comment('用户id');
            $table->unsignedInteger('warehouse_id')->comment('仓库ID');
            $table->unsignedInteger('operator')->comment('操作人');
            $table->tinyInteger('status')->comment('状态')->default(1);
            $table->unsignedInteger('need_num')->comment('需要的数量')->default(0);
            $table->unsignedInteger('confirm_num')->comment('实际的数量')->default(0);

            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->nullable()->comment('修改时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
            $table->index('warehouse_id'); 
            $table->unique(['warehouse_id', 'purchase_code']); 
            $table->index(['warehouse_id', 'order_invoice_number']); 
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase');
    }
}
