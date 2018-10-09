<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCertificationRentersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_certification_renters', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->integer('user_id')->comment('用户id');
            $table->string('warehouse_owner',255)->nullable()->comment('仓库产权方');
            $table->integer('status')->comment('认证状态 1 待审核 2 通过 3 驳回');
            $table->string('company_name_cn',255)->comment('公司名');
            $table->string('kvk_code',255)->nullable()->comment('KVK商会注册码');
            $table->string('vat_code',255)->nullable()->comment('VAT号码');
            $table->string('company_name_en',255)->comment('英文名称');
            $table->string('phone',255)->comment('联系电话');
            $table->string('country',255)->default(0)->comment('国家');
            $table->string('postcode',255)->comment('邮编');
            $table->string('door_no',255)->comment('门牌号');
            $table->string('city','255')->comment('城市');
            $table->string('street','255')->comment('街道');
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_certification_renters');
    }
}
