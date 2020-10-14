<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
//
class AddTotalPurchasePriceToBatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('batch', function (Blueprint $table)  {  
            $table->decimal('total_purchase_price',10,2)->comment('采购总价')->default(0);
        });

        DB::update('
update `batch`  
set
total_purchase_price = ifnull((select sum(purchase_price) from batch_product where batch_product.batch_id = `batch`.id), 0);

');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table)  {  
            $table->dropColumn('total_purchase_price');
        });
    }
}
