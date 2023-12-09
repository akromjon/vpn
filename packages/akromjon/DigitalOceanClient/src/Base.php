<?php

namespace Akromjon\DigitalOceanClient;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class Base
{
    protected string $baseUrl='https://api.digitalocean.com/v2/';
    private function __construct(protected string $token)
    {
        $this->token = $token;
    }

    public static function connect(string $token): static
    {
        return new static($token);
    }

    protected function baseHTTP(string $method,string $route,array $requestBody=[]):Response|\Exception
    {
        $response=Http::withToken($this->token)->{$method}($this->baseUrl.$route,$requestBody);

        $this->checkResponse($response,$method,$route,$requestBody);

        return $response;
    }

    protected function checkResponse(Response $response,string $method,string $route,array $requestBody):Response|\Exception
    {
        $status=$response->status();

        if(!in_array($status,[200,201,202,204])){

            throw new \Exception($this->createExceptionMessage($status,$method,$route,$requestBody,$response->body()));

        }

        return $response;
    }

    private function createExceptionMessage(int $status,string $method,string $route,array $requestBody,string $responseBody):string
    {
        $requestBody=json_encode($requestBody);

        return "Status: $status, Method: {$method}, Route: {$route}, Request Body: {$requestBody}, Response: {$responseBody}";
    }



    protected function wrapInArray(array|null $data):array
    {
        if(is_null($data)){

            return [];

        }

        return collect($data)->all();
    }

    public function getProjectPurposes(): array
    {
        return [
            'Just trying out DigitalOcean',
            'Class project / Educational purposes',
            'Website or blog',
            'Web Application',
            'Service or API',
            'Mobile Application',
            'Machine learning / AI / Data processing',
            'IoT',
            'Operational / Developer tooling',
        ];
    }

    protected function getValueOrDefault(string|bool $value,string $defaultValue):string|bool
    {
        if(is_bool($value)){

            return $value ? true : false;

        }

        return $value != "" ? $value : $defaultValue;
    }
}
