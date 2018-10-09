<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\EaxService;

class EaxServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('eax', function ($app) {
            return new EaxService($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['eax'];
    }
}
