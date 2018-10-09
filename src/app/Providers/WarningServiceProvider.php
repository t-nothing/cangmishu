<?php

namespace App\Providers;

use App\Services\WarningService;
use Illuminate\Support\ServiceProvider;

class WarningServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function register()
    {
        $this->app->singleton('WarningService',function($app){
            return new WarningService($app);
        });
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}