<?php

namespace Akromjon\DigitalOceanClient;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

abstract class Base
{
    protected string $baseUrl='https://api.digitalocean.com/v2/';
    public function __construct(protected string $token)
    {
        $this->token = $token;
    }

    protected function baseHTTP(string $method,string $route,array $requestBody=[]):Response|\Exception
    {
        $response=Http::withToken($this->token)->{$method}($this->baseUrl.$route,$requestBody);

        $this->checkResponse($response,$method,$route,$requestBody);

        return $response;
    }

    protected function checkResponse(Response $response,string $method,string $route,array $requestBody):Response|\Exception
    {
        if(!in_array($response->status(),[200,201,204])){
            $requestBody=json_encode($requestBody);
            throw new \Exception("Code:{$response->status()}, Method: {$method}, Route: {$route}, Request Body: {$requestBody}, Response: {$response->body()}");
        }

        return $response;
    }

    protected function wrapInArray(array|null $data):array
    {
        if(is_null($data))
        {
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
