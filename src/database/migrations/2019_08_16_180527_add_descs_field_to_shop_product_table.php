<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDescsFieldToShopProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('shop_product', function (Blueprint $table)  {                 
            $table->json('descs')->nullable()->comment('描述图片');  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('shop_product', function (Blueprint $table) {
            $table->dropColumn('descs');
        });
    }
}
