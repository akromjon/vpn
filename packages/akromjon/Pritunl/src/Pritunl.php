<?php

namespace Akromjon\Pritunl;

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
}
