<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TrackingService;

class TrackingServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('TrackingService', function($app) {
            return new TrackingService($app);
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
