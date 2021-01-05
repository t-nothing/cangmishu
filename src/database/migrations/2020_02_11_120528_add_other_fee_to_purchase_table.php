<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class AddOtherFeeToPurchaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('purchase', function (Blueprint $table)  {  
            $table->decimal('other_fee',10,2)->comment('其它费用')->default(0);    
            $table->decimal('deposit_fee',10,2)->comment('订金')->default(0); 
            $table->json('attache_files')->comment('图片附件');               
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase', function (Blueprint $table)  {  
            $table->dropColumn('other_fee');
            $table->dropColumn('deposit_fee'); 
            $table->dropColumn('attache_files'); 
        });
    }
}
