<?php
namespace App\RestClient;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\BadResponseException;
use App\RestClient\RestClientException;
use App\RestClient\RequestQuery;

class RestClient
{
    public $option = [];
    public $request = []; // Request Option
    public $requestQuery;
    private $path = '';

    public function __construct(string $url)
    {
        $this->setUrl($url);
        $this->requestQuery = new RequestQuery();
    }

    protected function setUrl(string $url)
    {
        $parts = explode('/', rtrim($url, '/'));
        if(count($parts) <= 3) {
            $host = $url;
            $path = '';
        } else {
            $host = $parts[0] . '//' . $parts[2];
            $path = implode('/', array_slice($parts, 3));
        }
        
        $this->option['base_uri'] = $host;
        $this->path = $path;
    }

    protected function getUrl()
    {
        if(empty($this->path)) {
            return $this->option['base_uri'];
        }
        return $this->option['base_uri'].'/'.$this->path;
    }

    public function sendRequest(string $httpMethod, string $pathUrl, $associative = null)
    {
        $client = new Client($this->option);
        $pathUrl = $this->path.$pathUrl;

        $requestOption = $this->request;
        if(isset($requestOption['query'])) {
            $requestOption['query'] = array_merge($requestOption['query'], $this->requestQuery->toArray());
        } else {
            $requestOption['query'] = $this->requestQuery->toArray();
        }

        try {

            $this->response = $client->request($httpMethod, $pathUrl, $requestOption);
            $body = $this->response->getBody();
            return json_decode($body, $associative);
        
        } catch (BadResponseException $err) {
            throw new RestClientException($err);
        }
    }
}