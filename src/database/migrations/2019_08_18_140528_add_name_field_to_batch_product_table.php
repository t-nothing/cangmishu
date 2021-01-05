<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameFieldToBatchProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('batch_product', function (Blueprint $table)  {                 
            $table->string('name_cn')->comment('中文名称');            
            $table->string('name_en')->comment('英文名称');            
            $table->string('sku')->comment('sku');          
            $table->decimal('purchase_price', 10, 2)->comment('采购价格')->default(0);
            $table->string('purchase_currency')->nullable()->comment('采购货币');
            $table->string('production_batch_number')->nullable()->comment('生产批次号');
            $table->integer('expiration_date')->nullable()->comment('保质期');
            $table->integer('best_before_date')->nullable()->comment('最佳食用期');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('batch_product', function (Blueprint $table) {
            $table->dropColumn('name_cn');
            $table->dropColumn('name_en');
            $table->dropColumn('sku');
            $table->dropColumn('purchase_price');
            $table->dropColumn('purchase_currency');
        });
    }
}
