<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQrCodeFieldToShopProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('shop_product', function (Blueprint $table)  {                 
            $table->string('weapp_qrcode')->comment('店铺商品二维码')->nullable(); 
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
            $table->dropColumn('weapp_qrcode');
        });
    }
}
