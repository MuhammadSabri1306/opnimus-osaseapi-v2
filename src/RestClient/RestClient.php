<?php
namespace App\RestClient;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use App\RestClient\RestClientException;

class RestClient
{
    public $option = [];
    public $request = []; // Request Option
    private $path = '';

    public function __construct(string $url)
    {
        $this->setUrl($url);
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
        try {

            $this->response = $client->request($httpMethod, $pathUrl, $requestOption);
            $body = $this->response->getBody();
            return json_decode($body, $associative);
        
        } catch (ClientException $err) {
            throw new RestClientException($err);
        }
    }
}