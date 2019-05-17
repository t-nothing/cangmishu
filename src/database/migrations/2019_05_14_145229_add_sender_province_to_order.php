<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSenderProvinceToOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order', function (Blueprint $table) {
            $table->string('send_province')->nullable();
            $table->renameColumn('receiver_doorno','receiver_district');
            $table->renameColumn('send_doorno','send_district');
        });

        Schema::table('warehouse', function (Blueprint $table) {
            $table->dropUnique('warehouse_code_unique');
            $table->dropUnique('warehouse_name_cn_unique');
            $table->dropUnique('warehouse_name_en_unique');
            $table->unique(['code','owner_id']);
            $table->unique(['name_cn','owner_id']);
            $table->unique(['name_en','owner_id']);
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
