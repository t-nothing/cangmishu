<?php

namespace App\Providers;

use DB;
use App\Services\BatchService;
use App\Services\GroupService;
use App\Services\ModuleService;
use App\Services\Service\CategoryService;
use App\Services\EmployeeService;
use App\Services\Service\OrderService;
use App\Services\Service\ProductService;
use App\Services\Service\ProductStockService;
use App\Services\Service\StoreService;
use App\Services\Service\ProductStockLogService;
use App\Services\Service\ShipService;
use App\Services\Service\RecountService;
use App\Services\Service\CartService;
use App\Services\UserService;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use App\Observers\CategoryObserver;
use App\Models\Category;
use App\Guard\ThirdParty;
use Illuminate\Auth\CreatesUserProviders;
use Auth;
use Illuminate\Support\Facades\Schema;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    use CreatesUserProviders;
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //设置域名
        app(UrlGenerator::class)->forceRootUrl(config('app.url'));
        Category::observe(CategoryObserver::class);
        if ( env('APP_ENV') === 'local' ) {
            \DB::listen(
                function ($sql) {
                    foreach ($sql->bindings as $i => $binding) {
                        if ($binding instanceof \DateTime) {
                            $sql->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                        } else {
                            if (is_string($binding)) {
                                $sql->bindings[$i] = "'$binding'";
                            }
                        }
                    }

                    // Insert bindings into query
                    $query = str_replace(array('%', '?'), array('%%', '%s'), $sql->sql);

                    $query = vsprintf($query, $sql->bindings);

                    // Save the query to file
                    $logFile = fopen(
                        storage_path('logs' . DIRECTORY_SEPARATOR . date('Y-m-d') . '_query.log'),
                        'a+'
                    );
                    fwrite($logFile, date('Y-m-d H:i:s') . ': ' . $query . PHP_EOL);
                    fclose($logFile);
                }
            );

        }

        Auth::extend('third-party', function ($app, $name, $config) {
            $guard = new ThirdParty(
                $this->createUserProvider($config['provider'] ?? null),
                $app['request'],
                'third-party-token',
                'third-party-token'
            );

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });

        \Validator::extend('mobile', function ($attribute, $value, $parameters, $validator) {
            return $validator->validateRegex($attribute, $value, ['/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\d{8}$/']);
        });


        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('batch',function(){
            return new BatchService();
        });
        $this->app->singleton('category',function(){
            return new CategoryService();
        });
        $this->app->singleton('group',function(){
            return new GroupService();
        });
        $this->app->singleton('order',function(){
            return new OrderService();
        });
        $this->app->singleton('stock',function(){
            return new ProductStockService();
        });
        $this->app->singleton('stockLog',function(){
            return new ProductStockLogService();
        });
        
        $this->app->singleton('product',function(){
            return new ProductService();
        });
        $this->app->singleton('store',function(){
            return new StoreService();
        });
        $this->app->singleton('user',function(){
            return new UserService();
        });
        $this->app->singleton('employee',function(){
            return new EmployeeService();
        });
        $this->app->singleton('module',function(){
            return new ModuleService();
        });
        $this->app->singleton('ship',function(){
            return new ShipService();
        });
        $this->app->singleton('recount',function(){
            return new RecountService();
        });
        $this->app->singleton('cart',function(){
            return new CartService();
        });
    }
}
