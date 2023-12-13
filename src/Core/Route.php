<?php
namespace App\Core;

use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponseArgs;

class Route
{
    protected static $app;

    public static function setup()
    {
        static::$app = AppFactory::create();
        static::$app->addRoutingMiddleware();
        static::$app->add(new BasePathMiddleware(static::$app));
        static::$app->addErrorMiddleware(true, true, true);

        $basePath = env('BASE_PATH', '/');
        static::$app->setBasePath($basePath);

        $routeCollector = static::$app->getRouteCollector();
        $routeCollector->setDefaultInvocationStrategy(new RequestResponseArgs());
    }

    public static function register(callable $routeCalls)
    {
        $routeCalls(static::$app);
    }

    public static function serve()
    {
        static::$app->run();
    }
}

Route::setup();