<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->string('name_cn');
            $table->string('name_en');
            $table->integer('category_id');
            $table->string('hs_code');
            $table->tinyInteger('storage_compartment');
            $table->string('origin', 20);
            $table->string('display_link', 1024);
            $table->string('remark');
            $table->string('photos');
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
            $table->integer('deleted_at')->nullable();
        });

        Schema::create('product_spec', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->integer('product_id')->default(0);
            $table->string('spec_name')->comment('规格名称');
            $table->float('net_weight')->default(0.00)->comment('净重');
            $table->float('gross_weight')->default(0.00)->comment('毛重');
            $table->string('relevance_code');
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
            $table->integer('deleted_at')->nullable();
        });

        Schema::create('product_stock', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->tinyInteger('status')->default(0)->comment('状态');
            $table->string('sku')->comment('sku');
            $table->string('ean')->comment('ean码');
            $table->string('relevance_code')->nullable()->comment('货品外部编码');
            $table->integer('warehouse_id')->default(0)->comment('仓库');
            $table->string('batch_id')->default(0)->comment('批次号');
            $table->integer('spec_id')->default(0);
            $table->integer('tray_id')->default(0)->comment('托盘号');
            $table->integer('distributor_id')->default(0)->comment('供货商');
            $table->string('distributor_code')->comment('供货商货号');
            $table->string('box_code')->comment('箱子编码');
            $table->integer('need_num')->default(0)->comment('入库单数量');
            $table->integer('stockin_num')->default(0)->comment('入库数量');
            $table->integer('shelf_num')->default(0)->comment('上架数量');
            $table->string('production_batch_number')->nullable()->comment('生产批次号');
            $table->integer('expiration_date')->nullable()->comment('保质期');
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
            $table->integer('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product');
        Schema::dropIfExists('product_spec');
        Schema::dropIfExists('product_stock');
    }
}
