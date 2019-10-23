<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class CreatePurchaseItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_item', function (Blueprint $table)  {     
            $table->increments('id'); 
            $table->integer('purchase_id')->comment('采购单ID')->default(0);
            $table->integer('spec_id')->comment('规格ID');
            $table->integer('warehouse_id')->comment('仓库ID');
            $table->integer('need_num')->comment('申请采购数量');
            $table->integer('confirm_num')->comment('实际数量');
            $table->date('last_confirm_date')->comment('最后确认日期');
            $table->string('product_spec_name', 100)->comment('商品规格名称');
            $table->string('relevance_code', 100)->comment('货品外部编码');
            $table->integer('status')->comment('1代入库,2 入库成功');
            $table->integer('owner_id')->comment('所属用户');

            $table->decimal('purchase_price', 10, 2)->comment('采购价格');
            $table->string('purchase_currency')->comment('采购货币');

            $table->index(['purchase_id']);

            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->nullable()->comment('修改时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_item');
    }
}
