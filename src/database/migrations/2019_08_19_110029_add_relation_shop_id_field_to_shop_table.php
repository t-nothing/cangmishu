<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRelationShopIdFieldToshopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('shop', function (Blueprint $table)  {                 
            $table->integer('relation_shop_id')->comment('镜像店铺ID')->default(0);
            // $table->index('relation_shop_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('shop', function (Blueprint $table) {
            $table->dropColumn('relation_shop_id');
            // $table->dropIndex('relation_shop_id');
        });
    }
}
