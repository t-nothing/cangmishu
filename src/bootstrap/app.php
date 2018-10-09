<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->alias(
    'mailer',
    Illuminate\Contracts\Mail\Mailer::class
);

$app->singleton('mailer', function () use ($app) {
    return $app->loadComponent('mail', Illuminate\Mail\MailServiceProvider::class, 'mailer');
});

$app->singleton('queue', function () use ($app) {
    return $app->loadComponent('queue', Illuminate\Queue\QueueServiceProvider::class, 'queue');
});

$app->alias(
    'filesystem',
    Illuminate\Contracts\Filesystem\Factory::class
);

$app->singleton('filesystem', function ($app) {
    return $app->loadComponent(
        'filesystems',
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        'filesystem'
    );
});

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//    App\Http\Middleware\ExampleMiddleware::class
// ]);

$app->configure('cors');
$app->middleware([
    App\Http\Middleware\Cors::class,
    App\Http\Middleware\Localization::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    // 'admin' => App\Http\Middleware\Admin::class,
    'can' => App\Http\Middleware\Authorize::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

/*
 * Laravel Framework Service Providers...
 */
$app->register(Illuminate\Redis\RedisServiceProvider::class);

/*
 * Application Service Providers...
 */
$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);
$app->register(App\Providers\MallServiceProvider::class);
$app->register(App\Providers\PostnlServiceProvider::class);
$app->register(App\Providers\HomePageProvider::class);
$app->register(App\Providers\BatchStatisticsProvider::class);
$app->register(App\Providers\EaxServiceProvider::class);
$app->register(App\Providers\OrderStatisticsProvider::class);
$app->register(App\Providers\StockStatisticsProvider::class);
$app->register(App\Providers\WarningServiceProvider::class);
$app->register(App\Providers\TrackingServiceProvider::class);

/*
 * Package Service Providers...
 */
$app->register(GrahamCampbell\Flysystem\FlysystemServiceProvider::class);
$app->register(Milon\Barcode\BarcodeServiceProvider::class);
$app->register(Overtrue\LaravelPinyin\ServiceProvider::class);
$app->register(Intervention\Image\ImageServiceProviderLumen::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
