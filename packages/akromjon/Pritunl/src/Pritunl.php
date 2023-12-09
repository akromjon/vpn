<?php

namespace Akromjon\Pritunl;

use Akromjon\Pritunl\Cloud\SSH\SSH;
use Illuminate\Http\Client\Response;
use Akromjon\Pritunl\BaseHttp;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Pritunl extends BaseHttp
{
    public function organizations(): array
    {
        return $this->baseHttp('get', 'organization/')->json();
    }

    public function organization(string $organizationId): array
    {
        return $this->baseHttp('get', "organization/{$organizationId}/")->json();
    }
    public function addOrganization(string $name): array
    {

        $params = [
            'name' => $name,
            'user_count' => null,
            'id' => null,
        ];

        return $this->baseHttp('post', 'organization/', $params)->json();
    }
    public function deleteOrganization(string $organizationId): array
    {
        return $this->baseHttp('delete', "organization/{$organizationId}/")->json();
    }
    public function users(string $organizationId): array
    {
        return $this->baseHttp('get', "user/{$organizationId}/",['status'=>true])->json();
    }
    public function addUser(string $organizationId, string $name): array
    {

        $params = [
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

        return $this->baseHttp('post', "user/{$organizationId}/", $params)->json();
    }

    public function deleteUser(string $organizationId, string $userId): array
    {
        return $this->baseHttp('delete', "user/{$organizationId}/{$userId}/")->json();
    }

    public function onlineUsers(string $organizationId):array
    {
        $users=$this->baseHttp('get', "user/{$organizationId}/")->json();

        return collect($users)->filter(fn($user) => $user['status'] === true)->all();
    }

    public function download(string $organizationId, string $userId): BinaryFileResponse|Response
    {
        $response = $this->baseHttp('get', "key/{$organizationId}/{$userId}.tar");

        $config = new VPNConfig($this->ip, $organizationId, $userId);

        return response()->download($config->download($response->body()));
    }

    public function getDownloadFilePath(string $organizationId, string $userId): string
    {
        $config = new VPNConfig($this->ip, $organizationId, $userId);

        return $config->getExtractedFile();

    }
    public function log(): array
    {
        return $this->baseHttp('get', 'log/')->json();

    }

    public function servers(): array
    {
        return $this->baseHttp('get', 'server/')->json();
    }

    public function server(string $serverId): array
    {
        return $this->baseHttp('get', "server/{$serverId}/")->json();
    }

    public function stopServer(string $serverId): array
    {
        return $this->baseHttp('put', "server/{$serverId}/operation/stop")->json();
    }

    public function startServer(string $serverId): array
    {
        return $this->baseHttp('put', "server/{$serverId}/operation/start")->json();
    }

    public function restartServer(string $serverId): array
    {
        return $this->baseHttp('put', "server/{$serverId}/operation/restart")->json();
    }

    public function deleteServer(string $serverId): array
    {
        return $this->baseHttp('delete', "server/{$serverId}/")->json();
    }

    public function serverOrganization(string $serverId): array
    {
        return $this->baseHttp('get', "server/{$serverId}/organization/")->json();
    }

    public function addServer(string $name): array
    {
        return $this->baseHttp('post', "server/", $this->getServerPayload($name))->json();
    }

    public function attachOrganization(string $serverId, string $organizationId): array
    {
        return $this->baseHttp('put', "server/{$serverId}/organization/{$organizationId}/")->json();
    }

    public function detachOrganization(string $serverId, string $organizationId): array
    {
        return $this->baseHttp('delete', "server/{$serverId}/organization/{$organizationId}/")->json();
    }

    public function settings():array
    {
        return $this->baseHttp('get', 'settings/')->json();
    }

    public function updateSettings(string $username, string $newPassword): array
    {
        $params = [
            "username" => $username,
            "password" => $newPassword,
        ];

       return $this->baseHttp('put', 'settings/', $params)->json();
    }

    public function setPinMode(string $mode): array
    {
        return $this->baseHttp('put', "settings/",["pin_mode" => $mode])->json();
    }

    public function activateSubscription(): array
    {
        return $this->baseHttp('post', 'subscription/', ['license' => "active ultimate"], 180)->json();
    }

    public function install(SSH $ssh, string $username, string $password): array
    {
        $this->installPritunl($ssh);

        $this->generateSetUpKey($ssh);

        $this->requestKey();

        $this->generateDefaultCredentials($ssh);

        $this->updateSettings($username, $password);

        $this->installFakeAPI($ssh);

        $ssh->disconnect();

        return $this->activateSubscription();

    }

}
