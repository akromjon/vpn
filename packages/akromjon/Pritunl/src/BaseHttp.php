<?php

namespace Akromjon\Pritunl;

use Akromjon\Pritunl\Cloud\SSH\SSH;
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

        dd($this->username ."-".$this->password);


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

    protected function getServerPayload($name):array
    {
        return [
            "name"=> $name,
            "network"=> "192.168.231.0/24",
            "port"=> 10317,
            "protocol"=> "udp",
            "dh_param_bits"=> 2048,
            "ipv6_firewall"=> true,
            "dns_servers"=> [
              "8.8.8.8"
            ],
            "cipher"=> "aes128",
            "hash"=> "sha1",
            "inter_client"=> true,
            "restrict_routes"=> true,
            "vxlan"=> true,
            "id"=> null,
            "status"=> null,
            "uptime"=> null,
            "users_online"=> null,
            "devices_online"=> null,
            "user_count"=> null,
            "network_wg"=> "",
            "groups"=> [],
            "bind_address"=> null,
            "dynamic_firewall"=> false,
            "route_dns"=> false,
            "device_auth"=> false,
            "port_wg"=> null,
            "ipv6"=> false,
            "network_mode"=> "tunnel",
            "network_start"=> "",
            "network_end"=> "",
            "wg"=> false,
            "multi_device"=> false,
            "search_domain"=> null,
            "otp_auth"=> false,
            "sso_auth"=> false,
            "block_outside_dns"=> false,
            "jumbo_frames"=> null,
            "lzo_compression"=> null,
            "ping_interval"=> null,
            "ping_timeout"=> null,
            "link_ping_interval"=> null,
            "link_ping_timeout"=> null,
            "inactive_timeout"=> null,
            "session_timeout"=> null,
            "allowed_devices"=> null,
            "max_clients"=> null,
            "max_devices"=> null,
            "replica_count"=> 1,
            "dns_mapping"=> false,
            "debug"=> false,
            "pre_connect_msg"=> null,
            "mss_fix"=> null,
            "multihome"=> false
        ];
    }

    protected function ssh(int $port, string $username,string $password="",string $connectionType="key" ,string $privateKeyPath="ssh"): SSH
    {
        return new SSH($this->ip, $port, $username,$password,$connectionType,$privateKeyPath);
    }

    protected function filterCredentials(string $credentials):array|\Exception
    {

        $regexUsername = '/username: "(.*?)"/';
        $regexPassword = '/password: "(.*?)"/';

        preg_match($regexUsername, $credentials, $matchesUsername);
        preg_match($regexPassword, $credentials, $matchesPassword);

        // Check if matches were found
        if (isset($matchesUsername[1]) && isset($matchesPassword[1])) {
            // Extracted username and password
            $username = $matchesUsername[1];
            $password = $matchesPassword[1];

            return ['username'=>$username,'password'=>$password];
        }

        return new \Exception("Credentials not found");
    }



}
