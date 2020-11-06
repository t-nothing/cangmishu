<?php

namespace App\Providers;

use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');

        // Horizon::night();
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {

        Gate::define('viewHorizon', function ($user) {
            if ($this->app->environment() === 'local'
                || $this->app->environment() === 'development'
                || $this->app->environment() === 'dev'
            ) {
                return true;
            }
            
            session(['horizon-user' => $user->email]);

            $api_token = app('request')->get('api_token');

            $ok = in_array($user->email, [
                'hubinjie@nle-tech.com'
            ]);

            if($ok && !empty($api_token)) {
                session(['api_token'=> $api_token]);
            }

            return $ok;
        });
    }
}
