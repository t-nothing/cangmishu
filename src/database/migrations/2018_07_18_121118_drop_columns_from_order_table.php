<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnsFromOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order', function (Blueprint $table) {
            $table->dropColumn([
                'operator',
                'is_night',
                'is_weekend',
                'payment_fee',
                'total_fee',
                'coupon_fee',
                'coupon_name',
                'invoice_number',
                'invoice_title',
                'invoice_content',
                'vip_id',
                'shipment_num',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order', function (Blueprint $table) {
            //
        });
    }
}
