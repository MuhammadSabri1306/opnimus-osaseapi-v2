<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;

class ApiController
{
    public function toJsonResponse(Response $response, $data)
    {
        $response->getBody()->write( json_encode($data) );
        return $response->withHeader('Content-Type', 'application/json');
    }
}