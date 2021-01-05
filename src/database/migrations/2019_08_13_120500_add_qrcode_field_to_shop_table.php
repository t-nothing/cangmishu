<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQrcodeFieldToShopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('shop', function (Blueprint $table)  {
            $table->string('weapp_qrcode')->nullable()->comment('小程序二维码');
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
            $table->dropColumn('weapp_qrcode');
        });
    }
}
