<?php

namespace Akromjon\Pritunl;

use Akromjon\Pritunl\Cloud\SSH\SSH;
use Illuminate\Http\Client\Response;
use Akromjon\Pritunl\BaseHttp;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Pritunl extends BaseHttp
{
    public function organization(): Response
    {
        $response = $this->baseHttp('get', 'organization/');

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('get', 'organization/');
        }

        $this->checkStatus($response, 'Organization');

        return $response;

    }
    public function addOrganization(string $name): Response
    {

        $params = [
            'name' => $name,
            'user_count' => null,
            'id' => null,
        ];

        $response = $this->baseHttp('post', 'organization/', $params);

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('post', 'organization/', $params);
        }

        $this->checkStatus($response, 'Add Organization');

        return $response;

    }
    public function deleteOrganization(string $organizationId): Response
    {

        $response = $this->baseHttp('delete', "organization/{$organizationId}/");

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('delete', "organization/{$organizationId}/");
        }

        $this->checkStatus($response, 'Delete Organization');

        return $response;

    }
    public function users(string $organizationId): Response
    {
        $response = $this->baseHttp('get', "user/{$organizationId}/");

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('get', "user/{$organizationId}/");
        }

        $this->checkStatus($response, 'Users');

        return $response;

    }
    public function addUser(string $organizationId, string $name): Response
    {

        $params=[
            "id" => null,
            "organization" => $organizationId,
            "organization_name" => null,
            "name" => $name,
            "email" => null,
            "groups" => [],
            "last_active" => null,
            "gravatar" => null,
            "audit" => null,
            "type" => null,
            "auth_type" => "local",
            "yubico_id" => "",
            "status" => null,
            "sso" => null,
            "otp_auth" => null,
            "otp_secret" => null,
            "servers" => null,
            "disabled" => null,
            "network_links" => [],
            "dns_mapping" => null,
            "bypass_secondary" => false,
            "client_to_client" => false,
            "dns_servers" => [],
            "dns_suffix" => "",
            "port_forwarding" => [],
            "pin" => null,
            "devices" => null,
            "mac_addresses" => []
        ];

        $response = $this->baseHttp('post', "user/{$organizationId}/", $params);

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('post', "user/{$organizationId}/", $params);
        }

        $this->checkStatus($response, 'Add User');

        return $response;

    }

    public function deleteUser(string $organizationId,string $userId):Response
    {
        $response = $this->baseHttp('delete', "user/{$organizationId}/{$userId}/");

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('delete', "user/{$organizationId}/{$userId}/");
        }

        $this->checkStatus($response, 'Delete User');

        return $response;
    }

    public function download(string $organizationId,string $userId):BinaryFileResponse|Response
    {
        $response = $this->baseHttp('get', "key/{$organizationId}/{$userId}.tar");

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('get', "key/{$organizationId}/{$userId}.tar");
        }

        $this->checkStatus($response, 'Download User');

        $config=new VPNConfig($this->ip,$organizationId,$userId);

       return response()->download($config->download($response->body()));
    }

    public function getDownloadFilePath(string $organizationId,string $userId):string
    {
        $config=new VPNConfig($this->ip,$organizationId,$userId);

        return $config->getExtractedFile();

    }
    public function log(): Response
    {
        $response = $this->baseHttp('get', 'log/');

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('get', 'log/');
        }

        return $response;

    }

    public function servers(): Response
    {
        $response = $this->baseHttp('get', 'server/');

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('get', 'server/');
        }

        $this->checkStatus($response, 'Servers');

        return $response;

    }

    public function stopServer(string $serverId): Response
    {
        $response = $this->baseHttp('put', "server/{$serverId}/operation/stop");

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('put', "server/{$serverId}/operation/stop");
        }

        $this->checkStatus($response, 'Stop Server');

        return $response;

    }

    public function startServer(string $serverId): Response
    {
        $response = $this->baseHttp('put', "server/{$serverId}/operation/start");

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('put', "server/{$serverId}/operation/start");
        }

        $this->checkStatus($response, 'Start Server');

        return $response;

    }

    public function restartServer(string $serverId): Response
    {
        $response = $this->baseHttp('put', "server/{$serverId}/operation/restart");

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('put', "server/{$serverId}/operation/restart");
        }

        $this->checkStatus($response, 'Restart Server');

        return $response;

    }

    public function deleteServer(string $serverId): Response
    {
        $response = $this->baseHttp('delete', "server/{$serverId}/");

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('delete', "server/{$serverId}/");
        }

        $this->checkStatus($response, 'Delete Server');

        return $response;

    }

    public function addServer(string $name):Response
    {
        $payload=$this->getServerPayload($name);

        $response = $this->baseHttp('post', "server/",$payload);

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('post', "server",$payload);
        }

        $this->checkStatus($response, 'Add Server');

        return $response;
    }

    public function attachOrganization(string $serverId,string $organizationId):Response
    {
        $response = $this->baseHttp('put', "server/{$serverId}/organization/{$organizationId}/");

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('put', "server/{$serverId}/organization/{$organizationId}/");
        }

        $this->checkStatus($response, 'Attach Organization');

        return $response;
    }

    public function detachOrganization(string $serverId,string $organizationId):Response
    {
        $response = $this->baseHttp('delete', "server/{$serverId}/organization/{$organizationId}/");

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('delete', "server/{$serverId}/organization/{$organizationId}/");
        }

        $this->checkStatus($response, 'Detach Organization');

        return $response;
    }

    public function settings()
    {
        $response = $this->baseHttp('get', 'settings/');

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('get', 'settings/');
        }

        $this->checkStatus($response, 'Settings');

        return $response;
    }

    public function updateSettings(string $username,string $newPassword):Response
    {
        $params=[
            "username" => $username,
            "password" => $newPassword,
        ];

        $response = $this->baseHttp('put', 'settings/',$params);

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('put', 'settings/',$params);
        }

        $this->checkStatus($response, 'Update Settings');

        return $response;
    }

    public function setPinMode(string $mode):Response
    {
        $params=[
            "pin_mode" => $mode,
        ];

        $response = $this->baseHttp('put', "settings/",$params);

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('put', "settings/",$params);
        }

        $this->checkStatus($response, 'Set Pin Mode');

        return $response;
    }

    public function activateSubscription():Response
    {
        $params=[
            'license' =>"active ultimate"
        ];

        $response = $this->baseHttp('post', 'subscription/', $params);

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('post', 'subscription/', $params);
        }

        $this->checkStatus($response, 'Active Subscriptions');

        return $response;
    }

    public function self(string $ip,string $username,string $password):self
    {
        return new self($ip,$username,$password);
    }

    public function install(int $port, string $username,string $password="",string $connectionType="key" ,string $privateKeyPath="ssh"):SSH
    {
        $ssh=$this->ssh($port,$username,$password,$connectionType,$privateKeyPath);

        if(!$ssh->connect()){

            throw new \Exception("SSH connection failed");

        }

        $ssh->setTimeout(0);

        // $ssh->exec('wget -O - https://raw.githubusercontent.com/akromjon/pritunl-ubuntu-22-04/main/install-pritunl.sh | bash');

        // $ssh->exec('sudo systemctl start pritunl');


        // $result=str_replace("\n","",$result);

        // Headers::write($this->ip,'set-upk-ey',$result);

        // $this->setUp();

        // $result=$ssh->exec("sudo pritunl default-password");

        // $credentials=$this->filterCredentials($result);

        // Headers::write($this->ip,'default-password',$credentials);

        $credentials=Headers::read($this->ip,'default-password');




        $pritunl=$this->self($this->ip,$credentials['username'],$credentials['password']);

        $pritunl=$this->updateSettings($credentials['username'],$credentials['password']);

        $ssh->disconnect();

        return $ssh;
    }

    public function setUp()
    {
        $params=[
            "setup_key" => Headers::read($this->ip,'setupkey'),
            "mongodb_uri" => "mongodb://localhost:27017/pritunl",
        ];

        $response = $this->baseHttp('post', 'setup/mongodb',$params);

        if (401 == $response->status()) {

            $this->login();

            return $this->baseHttp('post', 'setup/mongodb',$params);
        }


        $this->checkStatus($response, 'Setup');

        return $response;
    }


}
