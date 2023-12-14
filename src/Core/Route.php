<?php
namespace App\Core;

// use Selective\BasePath\BasePathMiddleware;
// use Slim\Factory\AppFactory;
// use Slim\Handlers\Strategies\RequestResponseArgs;

class Route
{
    protected static $app;

    public static function setup()
    {
        // static::$app = AppFactory::create();
        // static::$app->addRoutingMiddleware();
        // static::$app->add(new BasePathMiddleware(static::$app));
        // static::$app->addErrorMiddleware(true, true, true);

        // $basePath = env('BASE_PATH', '/');
        // static::$app->setBasePath($basePath);

        // $routeCollector = static::$app->getRouteCollector();
        // $routeCollector->setDefaultInvocationStrategy(new RequestResponseArgs());

        $config = new \Slim\Container();
        $config['foundHandler'] = function() {
            // return new \Slim\Handlers\Strategies\RequestResponseArgs();
            return new \App\Core\RequestResponseArgs();
        };

        $config['settings']['displayErrorDetails'] = true;

        Route::$app = new \Slim\App($config);

        // $basePath = env('BASE_PATH', '/');
        // static::$app->get('router')->setBasePath($basePath);
    }

    public static function register(callable $routeCalls)
    {
        $basePath = env('BASE_PATH', '/');

        if($basePath == '/') {
            $routeCalls(Route::$app);
        } else {
            Route::$app->group($basePath, fn() => $routeCalls(Route::$app));
        }
    }

    // public static function handleRoute(string $controller, string $method)
    // {
    //     return (new $controller())->$method
    // }

    public static function serve()
    {
        Route::$app->run();
    }
}

Route::setup();

// class HomeController
// {
//     public function index() {}
//     public function detail($userId) {}
// }

// function callHandler($controller, $method, $args) {
//     // add code here
// }

// $controller = HomeController::class;
// $method = 'index';

// callHandler(HomeController::class, 'index', [ 'userId' => 1 ])