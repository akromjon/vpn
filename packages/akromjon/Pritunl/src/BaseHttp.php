<?php

namespace Akromjon\Pritunl;

use Akromjon\Pritunl\Cloud\SSH\SSH;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseHttp
{
    protected string $ip;
    protected string $username;
    protected string $password;
    const RESTART_COMMAND = "sudo systemctl restart pritunl.service && sleep 10";
    public function __construct(string $ip, string $username, string $password)
    {
        $this->ip = $ip;
        $this->username = $username;
        $this->password = $password;
    }

    public static function connect(...$params): self
    {
        return new static(...$params);
    }

    protected function baseUrl(): string
    {
        return "https://{$this->ip}/";
    }

    protected function baseHttp(string $method, string $route, array $requestBody = [], int $timeout = 30): Response|\Exception
    {
        $response = Http::withOptions(['verify' => false])
                    ->timeout($timeout)
                    ->withHeaders([
                        'csrf-token' => Headers::read($this->ip, 'csrf-token'),
                        'Cookie' => Headers::read($this->ip, 'cookie')
                    ])->{$method}($this->baseUrl() . $route, $requestBody);

        $responseWithLogin = $this->checkResponse($response, $method, $route, $requestBody);

        if ($responseWithLogin instanceof Response) {
            return $responseWithLogin;
        }

        return $response;

    }



    protected function login(): void
    {
        $response=Http::withOptions(['verify' => false])->post($this->baseUrl() . "auth/session", [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if(401==$response->status() || 200!=$response->status()){
            throw new \Exception("Credentials are not valid: {$this->ip}, {$this->username}, {$this->password} with status: {$response->status()} body: {$response->body()} route: {$this->baseUrl()}/auth/session");
        }

        $cookie = $response->header('Set-Cookie');

        Headers::write($this->ip, 'cookie', $cookie);

        $token = $this->getCsrfToken();

        Headers::write($this->ip, 'csrf-token', $token);
    }

    protected function getCsrfToken(): string
    {
        $response = $this->baseHttp('get', 'state/');

        return $response->json()['csrf_token'];
    }

    private function createExceptionMessage(int $status, string $method, string $route, array $requestBody, string $responseBody): string
    {
        $requestBody = json_encode($requestBody);

        return "Status: $status, Method: {$method}, Route: {$route}, Request Body: {$requestBody}, Response: {$responseBody}";
    }
    protected function checkResponse(Response $response, string $method, string $route, array $requestBody): Response|\Exception
    {
        $status = $response->status();

        if (401 == $status) {

            $this->login();

            return $this->baseHttp($method, $route, $requestBody);
        }

        if (!in_array($status, [200, 201, 202, 204])) {

            throw new \Exception($this->createExceptionMessage($status, $method, $route, $requestBody, $response->body()));

        }

        return $response;
    }

    protected function checkStatus(Response $response, string $param = "")
    {
        if (404 == $response->status()) {
            throw new \Exception("{$param} not found for {$this->ip} with status {$response->status()}");
        }

        if (200 !== $response->status()) {
            throw new \Exception("{$this->ip} with status {$response->status()}");
        }

    }

    protected function getServerPayload($name): array
    {
        return [
            "name" => $name,
            "network" => "192.168.231.0/24",
            "port" => 10317,
            "protocol" => "udp",
            "dh_param_bits" => 2048,
            "ipv6_firewall" => true,
            "dns_servers" => [
                "8.8.8.8"
            ],
            "cipher" => "aes128",
            "hash" => "sha1",
            "inter_client" => true,
            "restrict_routes" => true,
            "vxlan" => true,
            "id" => null,
            "status" => null,
            "uptime" => null,
            "users_online" => null,
            "devices_online" => null,
            "user_count" => null,
            "network_wg" => "",
            "groups" => [],
            "bind_address" => null,
            "dynamic_firewall" => false,
            "route_dns" => false,
            "device_auth" => false,
            "port_wg" => null,
            "ipv6" => false,
            "network_mode" => "tunnel",
            "network_start" => "",
            "network_end" => "",
            "wg" => false,
            "multi_device" => false,
            "search_domain" => null,
            "otp_auth" => false,
            "sso_auth" => false,
            "block_outside_dns" => false,
            "jumbo_frames" => null,
            "lzo_compression" => null,
            "ping_interval" => null,
            "ping_timeout" => null,
            "link_ping_interval" => null,
            "link_ping_timeout" => null,
            "inactive_timeout" => null,
            "session_timeout" => null,
            "allowed_devices" => null,
            "max_clients" => null,
            "max_devices" => null,
            "replica_count" => 1,
            "dns_mapping" => false,
            "debug" => false,
            "pre_connect_msg" => null,
            "mss_fix" => null,
            "multihome" => false
        ];
    }

    protected function filterCredentials(string $credentials): array|\Exception
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

            return ['username' => $username, 'password' => $password];
        }

        return new \Exception("Credentials not found");
    }

    protected function installPritunl(SSH $ssh): SSH
    {
        $ssh->connect();

        $ssh->setTimeout(0);

        Log::info("starting install-pritunl.sh");

        $output = $ssh->exec('wget -O - https://raw.githubusercontent.com/akromjon/pritunl-ubuntu-22-04/main/install-pritunl.sh | bash');

        Log::info("output of install-pritunl.sh", ['output' => $output]);

        $ssh->exec(self::RESTART_COMMAND);

        return $ssh;
    }

    protected function generateSetUpKey(SSH $ssh): SSH
    {
        $result = $ssh->exec('sudo pritunl setup-key');

        Log::info("sudo pritunl setup-key", ['output' => $result]);

        Headers::write($this->ip, 'set-up-key', str_replace("\n", "", $result));

        return $ssh;
    }

    protected function generateDefaultCredentials(SSH $ssh): SSH
    {
        $result = $ssh->exec("sudo pritunl default-password");

        Log::info("sudo pritunl default-password", ['output' => $result]);

        Headers::write($this->ip, 'default-password', $this->filterCredentials($result));

        $this->resetLoginAndPassword();

        $ssh->exec(self::RESTART_COMMAND);

        return $ssh;
    }

    protected function requestKey(): array
    {
        $params = [
            "setup_key" => Headers::read($this->ip, 'set-up-key'),
            "mongodb_uri" => "mongodb://localhost:27017/pritunl",
        ];

        return $this->baseHttp('put', 'setup/mongodb', $params, 180)->json();
    }

    protected function installFakeAPI(SSH $ssh): SSH
    {
        $output = $ssh->exec("curl -sSL https://raw.githubusercontent.com/akromjon/Pritunl-Fake-API/master/server/setup.py | python3 -");

        Log::info("setup.py", ['output' => $output]);

        $result = $ssh->exec(self::RESTART_COMMAND);

        Log::info(self::RESTART_COMMAND, ['output' => $result]);

        return $ssh;
    }

    protected function resetLoginAndPassword()
    {
        $credentials = Headers::read($this->ip, 'default-password');

        $this->username = $credentials['username'];
        $this->password = $credentials['password'];
    }



}
