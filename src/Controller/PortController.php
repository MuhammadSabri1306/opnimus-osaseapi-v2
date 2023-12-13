<?php
namespace App\Controller;

class PortController extends ApiController
{
    public function index($request, $response)
    {
        $params = $request->getQueryParams();
        return $this->toJsonResponse($response, [
            'params' => $params,
            'success' => true
        ]);
    }
}