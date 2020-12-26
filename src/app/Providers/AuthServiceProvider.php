<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Laravel\Passport\RouteRegistrar;
use App\Guard\JwtGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::extend('custom-jwt', function ($app, $name, array $config) {
            // 返回一个 Illuminate\Contracts\Auth\Guard 实例...

            return new JwtGuard(Auth::createUserProvider($config['provider']),app('request'));
        });

        Passport::routes(function (RouteRegistrar $router) {
            config(['auth.guards.api.provider' => 'users']);
            $router->forAccessTokens();
        });

        // accessToken有效期
        Passport::tokensExpireIn(Carbon::now()->addDays(15));
        // accessRefushToken有效期
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));


        // Passport::routes();

    }
}
