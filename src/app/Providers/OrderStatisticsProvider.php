<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/22
 * Time: 15:39
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\HomePage\OrderStatisticsService;

class OrderStatisticsProvider extends  ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('OrderStatisticsService',function($app){
            return new OrderStatisticsService($app);
        });
    }
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
        //使用singleton绑定单例


        //使用bind绑定实例到接口以便依赖注入
//        $this->app->bind('App\Contracts\TestContract',function(){
//            return new PaymentService();
//        });

    }
}