<?php
namespace App\Controller;

class HomeController extends ApiController
{
    public function index()
    {
        $docFileContent = file_get_contents(__DIR__ . '/../../public/documentation.xml');
        $response = $this->response;
        $response->getBody()->write($docFileContent);
        return $response->withHeader('Content-Type', 'text/xml');
    }
}