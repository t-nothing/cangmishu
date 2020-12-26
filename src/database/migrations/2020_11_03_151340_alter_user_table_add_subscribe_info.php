<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserTableAddSubscribeInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('app_openid')
                ->unique()
                ->nullable()
                ->comment('网站应用openID');
            $table->unsignedTinyInteger('is_subscribe')
                ->default(0)
                ->comment('是否订阅公众号');
            $table->timestamp('subscribed_at')
                ->nullable()
                ->comment('订阅时间');
            $table->unique(['union_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['app_openid', 'is_subscribed', 'subscribed_at']);
            $table->dropUnique(['union_id']);
        });
    }
}
