<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Core\Route;

use App\Controller\HomeController;
use App\Controller\PortController;
use App\Controller\RtuController;
use App\Controller\RtuStatusController;

Route::register(function ($app) {

    $app->get('/', [ HomeController::class, 'index' ]);

    $app->get('/getrtuport', [ PortController::class, 'index' ]);
    $app->get('/getrtulist', [ RtuController::class, 'index' ]);
    $app->get('/getrtustatus', [ RtuStatusController::class, 'index' ]);

});