<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class AddCategoryIdToShopProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('shop_product', function (Blueprint $table)  {  
            $table->integer('category_id')->comment('分类ID')->default(0);                 
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_product', function (Blueprint $table)  {  
            $table->dropColumn('category_id');                  
            $table->dropIndex(['category_id']);
        });
    }
}
