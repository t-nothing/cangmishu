<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSortNumFieldToShopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('shop', function (Blueprint $table)  { 
            $table->integer('sort_num')->comment('推荐')->nullable()->default(0);
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
            $table->dropColumn('sort_num');            
            $table->dropIndex('spec_name_en');            
        });
    }
}
