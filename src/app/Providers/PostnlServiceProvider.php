<?php
/**
 * DESC:postnl服务
 * Author: YangBin1024.
 * DateTime: 2017/9/19 17:18
 * Email: yangbin@nle-tech.com
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PostnlService;

class PostnlServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('PostnlService',function($app){
            return new PostnlService($app);
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