<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ApiController
{
    protected $request;
    protected $response;

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }
 
    public function toJsonResponse(array $data = [])
    {
        $response = $this->response;
        $response->getBody()->write( json_encode($data) );
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function toErrorJsonResponse(string $message = '', int $code = 200)
    {
        $response = $this->response;
        if($code != 200) {
            $response = $response->withStatus($code);
        }

        $data = [ 'error' => $message ];
        $response->getBody()->write( json_encode($data) );

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function isAuthorized()
    {
        $request = $this->request;
        $params = $request->getQueryParams();

        if(isset($params['token'])) {
            $appToken = env('SECURITY_TOKEN');
            return $params['token'] == $appToken;
        }

        return false;
    }
}