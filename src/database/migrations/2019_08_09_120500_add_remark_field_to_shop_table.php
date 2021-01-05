<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemarkFieldToShopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('shop', function (Blueprint $table)  {
            $table->text('remark_cn')->nullable();
            $table->text('remark_en')->nullable();
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
            $table->dropColumn('remark_cn');
            $table->dropColumn('remark_en');
        });
    }
}
