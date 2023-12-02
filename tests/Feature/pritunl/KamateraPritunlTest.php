<?php

namespace Tests\Feature;

use Akromjon\Pritunl\Headers;
use Akromjon\Pritunl\Pritunl;
use Tests\TestCase;
use Illuminate\Support\Str;

class KamateraPritunlTest extends TestCase
{

    protected Pritunl $pritunl;
    protected string $ip;
    protected string $username;
    protected string $password;
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpCredentials();

        $this->pritunl = new Pritunl($this->ip, $this->username, $this->password);

    }

    private function setUpCredentials()
    {
        $this->ip = env("TEST_KAMATERA_PRITUNL_IP");

        $this->username =env("TEST_KAMATERA_PRITUNL_USERNAME");

        $this->password =env("TEST_KAMATERA_PRITUNL_PASSWORD");
    }



    public function test_kamatera_it_fails_with_wrong_credentials()
    {
        $this->expectException(\Exception::class);

        Headers::clean();

        $pritunl = new Pritunl($this->ip, $this->username, 'wrong_password');

        $pritunl->organization();
    }
    public function test_kamatera_it_can_login()
    {
        $pritunl = $this->pritunl;

        $this->assertEquals(200, $pritunl->organization()->status());
    }

    public function test_kamatera_it_can_get_404_exception_in_users_the_route()
    {
        $this->expectException(\Exception::class);

        $pritunl = $this->pritunl;

        $pritunl->users("123");
    }

    public function test_kamatera_it_can_get_organizations()
    {
        $pritunl = $this->pritunl;

        $organizations = $pritunl->organization()->json();

        $this->assertIsArray($organizations);

    }

    public function test_kamatera_it_can_get_users_by_organization_id()
    {
        $pritunl = $this->pritunl;

        $users = $pritunl->users("6567162cb8915c9dd36bd0c9");

        logger($users->body());

        $this->assertIsArray($users->json());
    }

    public function test_kamatera_it_can_add_organization()
    {
        $pritunl = $this->pritunl;

        $random = Str::uuid()->toString();

        $pritunl->addOrganization($random);

        $this->assertIsArray($pritunl->organization()->json());

        $this->assertStringContainsString($random, $pritunl->organization()->body());
    }

    public function test_kamatera_it_deletes_organization()
    {

        $pritunl = $this->pritunl;

        $random = Str::uuid()->toString();

        $addResponse = $pritunl->addOrganization($random);

        $this->assertEquals(200, $addResponse->status());

        $deleteResponse = $pritunl->deleteOrganization($addResponse->json()['id']);

        $this->assertEquals(200, $deleteResponse->status());

        $this->assertStringNotContainsString($random, $deleteResponse->body());
    }

    public function test_kamatera_it_can_add_user()
    {

        $pritunl = $this->pritunl;

        $organizations = $pritunl->organization();

        $organizationId = $organizations->json()[0]['id'];

        $random = Str::uuid()->toString();

        $addResponse = $pritunl->addUser($organizationId, $random);

        $this->assertStringContainsString($random, $addResponse->body());
    }

    public function test_kamatera_it_can_delete_user()
    {
        $random = Str::uuid()->toString();

        $pritunl = $this->pritunl;

        $organization = $pritunl->organization();

        $organizationId = $organization->json()[0]['id'];

        $addResponse = $pritunl->addUser($organizationId, $random);

        $this->assertStringContainsString($random, $addResponse->body());

        $userId = $addResponse->json()[0]['id'];

        $deleteResponse = $pritunl->deleteUser($organizationId, $userId);

        $this->assertEquals(200, $deleteResponse->status());

        $this->assertStringNotContainsString($random, $pritunl->users($organizationId)->body());

    }

    public function test_kamatera_it_can_download(){

        $pritunl = $this->pritunl;

        $organizationId = "6567162cb8915c9dd36bd0c9";

        $userId = "65671630b8915c9dd36bd0d8";

        $downloadResponse = $pritunl->download($organizationId,$userId);

        $this->assertStringContainsString("config.ovpn",$downloadResponse);

    }

    public function test_kamatera_it_can_get_get_Download_File_Path()
    {
        $pritunl = $this->pritunl;

        $organizationId = "6567162cb8915c9dd36bd0c9";

        $userId = "65671630b8915c9dd36bd0d8";

        $downloadResponse = $pritunl->getDownloadFilePath($organizationId,$userId);

        $this->assertStringContainsString("config.ovpn",$downloadResponse);

        $this->assertStringEndsWith("config.ovpn",$downloadResponse);

    }

    public function test_kamatera_it_can_download_all_user_configs_by_one_organization()
    {
        $pritunl = $this->pritunl;

        $users=$pritunl->users("6567162cb8915c9dd36bd0c9")->json();;

        foreach ($users as $user)
        {
            $pritunl->download($user['organization'],$user['id']);

            $path=storage_path("servers/vpnconfigs/{$this->ip}/{$user['organization']}/{$user['id']}/config.ovpn");

            $this->assertFileExists($path);
        }
    }

    public function test_kamatera_it_can_get_a_list_of_servers()
    {
        $pritunl = $this->pritunl;

        $servers=$pritunl->servers()->body();

        $this->assertSame(200,$pritunl->servers()->status());

        $this->assertIsArray($servers);
    }

    public function test_kamatera_it_can_stop_server()
    {
        $pritunl = $this->pritunl;

        $servers=$pritunl->servers()->json();

        $serverId=$servers[0]['id'];

        $this->assertSame(200,$pritunl->stopServer($serverId)->status());
    }

    public function test_kamatera_it_can_start_server()
    {
        $pritunl = $this->pritunl;

        $servers=$pritunl->servers()->json();

        $serverId=$servers[0]['id'];

        $this->assertSame(200,$pritunl->startServer($serverId)->status());
    }

    public function test_kamatera_it_can_restart_server()
    {
        $pritunl = $this->pritunl;

        $servers=$pritunl->servers()->json();

        $serverId=$servers[0]['id'];

        $this->assertSame(200,$pritunl->restartServer($serverId)->status());
    }

    public function test_kamatera_it_can_delete_server()
    {
        $pritunl = $this->pritunl;

        $servers=$pritunl->servers()->json();

        $serverId=$servers[0]['id'];

        $this->assertSame(200,$pritunl->deleteServer($serverId)->status());
    }

    public function test_kamatera_it_can_add_server(){

        $pritunl = $this->pritunl;

        $random = Str::uuid()->toString();

        $response=$pritunl->addServer($random);

        $this->assertSame(200,$response->status());

        $this->assertStringContainsString($random,$response->body());
    }

    public function test_kamatera_it_can_attach_orginization_to_server(){

        $pritunl = $this->pritunl;

        $servers=$pritunl->servers()->json();

        $serverId=$servers[0]['id'];

        $organizations=$pritunl->organization()->json();

        $organizationId=$organizations[0]['id'];

        $response=$pritunl->attachOrganization($serverId,$organizationId);

        $this->assertSame(200,$response->status());

        $this->assertStringContainsString($organizationId,$response->body());
    }
    public function test_kamatera_it_can_detach_organization_from_server(){

        $pritunl = $this->pritunl;

        $servers=$pritunl->servers()->json();

        $serverId=$servers[0]['id'];

        $organizations=$pritunl->organization()->json();

        $organizationId=$organizations[0]['id'];

        $response=$pritunl->detachOrganization($serverId,$organizationId);

        $this->assertSame(200,$response->status());

        $this->assertStringNotContainsString($organizationId,$response->body());
    }

    public function test_kamatera_it_can_get_settings()
    {
        $pritunl = $this->pritunl;

        $response=$pritunl->settings();

        $this->assertSame(200,$response->status());

        $this->assertIsArray($response->json());
    }

    public function test_kamatera_it_can_update_settings()
    {
        $pritunl = $this->pritunl;

        $pritunl->updateSettings(env("TEST_PRITUNL_USERNAME"),env("TEST_PRITUNL_PASSWORD"));

        $this->assertSame(200,$pritunl->settings()->status());
    }

    public function test_it_can_set_pinmode_optional()
    {
        $pritunl = $this->pritunl;

        $pritunl->setPinMode('optional');

        $this->assertSame(200,$pritunl->settings()->status());
    }

    public function test_it_can_set_pinmode_required()
    {
        $pritunl = $this->pritunl;

        $pritunl->setPinMode('required');

        $this->assertSame(200,$pritunl->settings()->status());
    }


}
