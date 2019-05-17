<?php

namespace App\Providers;

use App\Services\BatchService;
use App\Services\GroupService;
use App\Services\Service\CategoryService;
use App\Services\Service\OrderService;
use App\Services\Service\ProductService;
use App\Services\Service\ProductStockService;
use App\Services\Service\StoreService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AllServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
//        $this->app->singleton('batch',function(){
//            return new BatchService();
//        });
//        $this->app->singleton('category',function(){
//            return new CategoryService();
//        });
//        $this->app->singleton('group',function(){
//            return new GroupService();
//        });
//        $this->app->singleton('order',function(){
//            return new OrderService();
//        });
//        $this->app->singleton('stock',function(){
//            return new ProductStockService();
//        });
//        $this->app->singleton('product',function(){
//            return new ProductService();
//        });
//        $this->app->singleton('store',function(){
//            return new StoreService();
//        });
//        $this->app->singleton('user',function(){
//            return new UserService();
//        });
//        $this->app->singleton('stock',function($app){
//            return new Stc($app);
//        });
    }
}
