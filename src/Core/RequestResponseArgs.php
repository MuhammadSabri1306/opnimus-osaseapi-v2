<?php
namespace App\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

/**
 * Route callback strategy with route parameters as individual arguments.
 */
class RequestResponseArgs implements InvocationStrategyInterface
{

    /**
     * Invoke a route callable with request, response and all route parameters
     * as individual arguments.
     *
     * @param array|callable         $callable
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $routeArguments
     *
     * @return ResponseInterface
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ) {
        // array_unshift($routeArguments, $request, $response);
        // return call_user_func_array($callable, $routeArguments);

        $controller = new $callable[0]($request, $response);
        $method = $callable[1];
        return $controller->$method(...$routeArguments);
    }
}