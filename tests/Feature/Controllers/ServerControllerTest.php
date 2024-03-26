<?php

namespace Tests\Feature\Controllers;


class ServerControllerTest extends \Tests\TestCase
{
    protected string $apiKey;
    protected string $version;
    public function setUp(): void
    {

        parent::setUp();

        $this->apiKey = config('app.api-key');

        $this->version = env('ANDROID_VERSION');
    }

    private function getDefaultHeaders(): array
    {

        $token = $this->test_it_can_generate_token();

        return [
            "TOKEN" => $token,
            "VERSION" => $this->version,
            "OS-TYPE" => "android",
        ];
    }
    public function test_it_can_generate_token()
    {
        $res = $this->withHeaders([
            'API-KEY' => $this->apiKey,
            'VERSION' => $this->version
        ])->json('POST', 'api/token', [
            "os_type" => "ios",
            "os_version" => "17.0,1",
            "model" => "iphone"
        ]);

        $this->assertEquals(200, $res->status());

        return $res->json('token');
    }
    public function test_it_can_list_servers()
    {
        $res = $this->withHeaders($this->getDefaultHeaders())->json('get', 'api/servers');

        $this->assertEquals(200, $res->status());

        return $res?->json();
    }

    public function test_it_can_download_config_file()
    {

        $first_ip_address_from_the_list = $this->test_it_can_list_servers()[0]['ip'];

        $res = $this->withHeaders($this->getDefaultHeaders())
            ->json("POST", "api/servers/{$first_ip_address_from_the_list}/download/");


        $file = $res->getFile()->getContent();

        $this->assertStringContainsString("UV_CLIENT_UUID", $file);


        return $file;
    }

    public function test_it_can_connect_to_server_from_servers(): array
    {
        $file = $this->test_it_can_download_config_file();

        preg_match('/"user_id"\s*:\s*"([^"]+)"/', $file, $user);

        preg_match('/setenv UV_CLIENT_UUID\s+([^\n]+)/', $file, $client_uuid);

        if (!isset($user[1]) && !isset($client_uuid[1])) {
            $this->assertTrue(false);
        }

        $uuid = $client_uuid[1];

        $user_id = $user[1];

        $default_headers = $this->getDefaultHeaders();

        $data = [
            'pritunl_user_id' => $user_id,
            'state' => "connected",
            'client_uuid' => $uuid,
        ];

        $res = $this->withHeaders($default_headers)->json("GET", "/api/action",$data);

        $this->assertEquals(200, $res->status());

        $this->assertSame($res->json('message'), $data['state']);

        return $data;
    }

    public function test_it_can_disconnect_from_server()
    {
        $data = $this->test_it_can_connect_to_server_from_servers();

        $data['state'] = 'disconnected';

        $res = $this->withHeaders($this->getDefaultHeaders())
            ->json("GET", "api/action", $data)
            ->assertOk();

        $this->assertSame($res->json('message'),  $data['state']);
    }
}
