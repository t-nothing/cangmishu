<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class CreateReceiverTypeTable extends Migration
{
    /**
     * 收件人分类
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receiver_type', function (Blueprint $table)  {     
            $table->increments('id'); 
            $table->integer('warehouse_id')->comment('仓库ID');
            $table->tinyInteger('is_allow_negative_inventory')->comment('允许负库存销售')->default(1);

            $table->index(['warehouse_id']);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receiver_type');
    }
}
