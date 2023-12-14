<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;

class ApiController
{
    public function toJsonResponse(Response $response, array $data = [])
    {
        $response->getBody()->write( json_encode($data) );
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function toErrorJsonResponse(Response $response, string $message = '', int $code = 200)
    {
        if($code != 200) {
            $response = $response->withStatus($code);
        }

        $data = [ 'error' => $message ];
        $response->getBody()->write( json_encode($data) );

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function isAuthorized(Response $response)
    {
        $params = $request->getQueryParams();

        if(isset($params['token'])) {
            $appToken = env('SECURITY_TOKEN');
            return $params['token'] == $appToken;
        }

        return false;
    }
}