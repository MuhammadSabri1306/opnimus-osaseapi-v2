<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Core\Route;

use App\Controller\HomeController;
use App\Controller\PortController;

Route::register(function ($app) {

    $app->get('/', [ HomeController::class, 'index' ]);
    $app->get('/detail/{id}', [ HomeController::class, 'index' ]);
    // $app->get('/', function ($request, $response) {
    //     return $response->withJson([ 'success' => true ]);
    // });
    // $app->get('/api/osasev2', $handle(HomeController::class, 'index'));

    $app->get('/getrtuport', [ PortController::class, 'index' ]);

});