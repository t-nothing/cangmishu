<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AddPriceFieldToProductOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();

        $checkIndexIfExists = function ($tableName, $indexName) use($sm)
        {
            $indexes = $sm->listTableIndexes($tableName);
            return array_key_exists("{$tableName}_{$indexName}_index", $indexes);
        };


        Schema::table('product_spec', function (Blueprint $table) use ($checkIndexIfExists) {
            $table->decimal('sale_price', 10, 2)->comment('销售价格');
            $table->decimal('purchase_price', 10, 2)->comment('采购价格');
            $table->string('sale_currency')->comment('销售货币');
            $table->string('purchase_currency')->comment('采购货币');
            if(!$checkIndexIfExists($table->getTable(),"product_id")) $table->index('product_id');
        });


        Schema::table('product', function (Blueprint $table) use ($checkIndexIfExists) {
            $table->decimal('sale_price', 10, 2)->comment('销售价格');
            $table->string('sale_currency')->comment('销售货币');
            $table->decimal('purchase_price', 10, 2)->comment('采购价格');
            $table->string('purchase_currency')->comment('采购货币');

            if(!$checkIndexIfExists($table->getTable(),"warehouse_id")) $table->index('warehouse_id');
        });

        Schema::table('order', function (Blueprint $table)   use ($checkIndexIfExists){
            $table->decimal('sub_total', 10, 2)->comment('应付价格');
            $table->decimal('sub_pay', 10, 2)->comment('实付价格');
            $table->string('pay_currency')->comment('实付货币');
            $table->integer('shop_id')->comment('店铺ID');

            if(!$checkIndexIfExists($table->getTable(),"shop_id")) $table->index('shop_id');
        });

        Schema::table('order_item', function (Blueprint $table)  use ($checkIndexIfExists) {
            $table->decimal('sale_price', 10, 2)->comment('销售价格');
            $table->string('sale_currency')->comment('销售货币');
            $table->decimal('purchase_price', 10, 2)->comment('采购价格');
            $table->string('purchase_currency')->comment('采购货币');
            if(!$checkIndexIfExists($table->getTable(),"order_id")) $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()  
    {
        Schema::table('product_spec', function (Blueprint $table) {
            $table->dropColumn('sale_price');
            $table->dropColumn('sale_currency');
            $table->dropColumn('purchase_price');
            $table->dropColumn('purchase_currency');
        });

        Schema::table('product', function (Blueprint $table) {
            $table->dropColumn('sale_price');
            $table->dropColumn('sale_currency');
            $table->dropColumn('purchase_price');
            $table->dropColumn('purchase_currency');
        });

        Schema::table('order', function (Blueprint $table) {
            $table->dropColumn('sub_total');
            $table->dropColumn('sub_pay');
            $table->dropColumn('pay_currency');
            $table->dropColumn('shop_id');
        });

        Schema::table('order_item', function (Blueprint $table) {
            $table->dropColumn('sale_price');
            $table->dropColumn('sale_currency');
            $table->dropColumn('purchase_price');
            $table->dropColumn('purchase_currency');
        });
    }
}
