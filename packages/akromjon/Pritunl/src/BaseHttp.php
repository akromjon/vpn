<?php

namespace Akromjon\Pritunl;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class BaseHttp
{
    protected string $ip;
    protected string $username;
    protected string $password;
    public function __construct(string $ip, string $username, string $password)
    {
        $this->ip = $ip;
        $this->username = $username;
        $this->password = $password;
    }

    protected function baseUrl():string
    {
        return "https://{$this->ip}/";
    }

    protected function baseHttp(string $method,string $route,array $requestBody=[]):Response
    {
        return Http::withOptions(['verify' => false])
            ->withHeaders([
            'csrf-token' => Headers::read($this->ip,'csrf-token'),
            'Cookie' => Headers::read($this->ip,'cookie')
        ])->{$method}($this->baseUrl().$route,$requestBody);
    }

    protected function login():void
    {

        $response = $this->baseHttp('post','auth/session/', [
            'username' => $this->username,
            'password' => $this->password,
        ]);


        if($response->status()!=200){
            throw new \Exception("Authentication failed for {$this->ip} with status {$response->status()}");
        }

        $cookie=$response->header('Set-Cookie');

        Headers::write($this->ip,'cookie',$cookie);

        $token=$this->getCsrfToken();

        Headers::write($this->ip,'csrf-token',$token);
    }

    protected function getCsrfToken():string
    {
        $response = $this->baseHttp('get','state/');

        return $response->json()['csrf_token'];
    }

    protected function checkStatus(Response $response,string $param=""){
        if(404==$response->status()){
            throw new \Exception("{$param} not found for {$this->ip} with status {$response->status()}");
        }

        if(200!==$response->status()){
           throw new \Exception("{$this->ip} with status {$response->status()}");
        }

    }
}
