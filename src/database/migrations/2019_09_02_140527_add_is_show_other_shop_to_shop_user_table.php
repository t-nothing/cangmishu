<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class AddIsShowOtherShopToShopUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('shop_user', function (Blueprint $table)  {      
            $table->integer('is_show_other_shop')->comment('显示其他店铺')->default(0);           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_user', function (Blueprint $table)  { 
            $table->dropColumn('is_show_other_shop');                 
        });
    }
}
