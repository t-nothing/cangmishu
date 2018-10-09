<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Auth\JwtGuard;
use App\Services\Auth\SignGuard;
// use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
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
        //
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

        // $this->app['auth']->viaRequest('api', function ($request) {
        //     if ($request->input('api_token')) {
        //         return User::where('api_token', $request->input('api_token'))->first();
        //     }
        // });

        app('auth')->extend('jwt', function () {
            $guard = new JwtGuard(app('auth')->createUserProvider(), app('request'));

            // $this->app->refresh('request', $guard, 'setRequest');

            return $guard;
        });

        app('auth')->extend('sign', function () {
            $guard = new SignGuard(app('auth')->createUserProvider(), app('request'));

            // $this->app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }
}
