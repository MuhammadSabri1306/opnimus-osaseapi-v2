<?php
namespace App\Controller;

class HomeController extends ApiController
{
    public function index($request, $response)
    {
        return $this->toJsonResponse($response, [ 'success' => true ]);
    }
}