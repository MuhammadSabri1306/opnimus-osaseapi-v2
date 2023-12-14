<?php
namespace App\Controller;

class HomeController extends ApiController
{
    public function index()
    {
        return $this->toJsonResponse([ 'success' => true ]);
    }
}