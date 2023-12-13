<?php
namespace App\RestClient;


use GuzzleHttp\Exception\ClientException;

class RestClientException extends \Exception
{
    public $request;
    public $response;

    public function __construct(ClientException $previous = null) {
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
        $this->request = json_decode($previous->getRequest()->getBody()->getContents());
        $this->response = json_decode($previous->getResponse()->getBody()->getContents());
    }
}