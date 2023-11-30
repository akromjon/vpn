<?php

namespace Tests\Feature;

use Akromjon\Pritunl\Headers;
use Akromjon\Pritunl\Pritunl;
use Tests\TestCase;
use Illuminate\Support\Str;

class PritunlTest extends TestCase
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
        $this->ip = env("TEST_PRITUNL_IP");

        $this->username =env("TEST_PRITUNL_USERNAME");

        $this->password =env("TEST_PRITUNL_PASSWORD");
    }



    public function test_it_fails_with_wrong_credentials()
    {
        $this->expectException(\Exception::class);

        Headers::clean();

        $pritunl = new Pritunl($this->ip, $this->username, 'wrong_password');

        $pritunl->organization();
    }
    public function test_it_can_login()
    {
        $pritunl = $this->pritunl;

        $this->assertEquals(200, $pritunl->organization()->status());
    }

    public function test_it_can_get_404_exception_in_users_the_route()
    {
        $this->expectException(\Exception::class);

        $pritunl = $this->pritunl;

        $pritunl->users("123");
    }

    public function test_it_can_get_organizations()
    {
        $pritunl = $this->pritunl;

        $organizations = $pritunl->organization()->json();

        $this->assertIsArray($organizations);

    }

    public function test_it_can_get_users_by_organization_id()
    {
        $pritunl = $this->pritunl;

        $users = $pritunl->users("6567162cb8915c9dd36bd0c9");

        $this->assertIsArray($users->json());
    }

    public function test_it_can_add_organization()
    {
        $pritunl = $this->pritunl;

        $random = Str::uuid()->toString();

        $pritunl->addOrganization($random);

        $this->assertIsArray($pritunl->organization()->json());

        $this->assertStringContainsString($random, $pritunl->organization()->body());
    }

    public function test_it_deletes_organization()
    {

        $pritunl = $this->pritunl;

        $random = Str::uuid()->toString();

        $addResponse = $pritunl->addOrganization($random);

        $this->assertEquals(200, $addResponse->status());

        $deleteResponse = $pritunl->deleteOrganization($addResponse->json()['id']);

        $this->assertEquals(200, $deleteResponse->status());

        $this->assertStringNotContainsString($random, $deleteResponse->body());
    }

    public function test_it_can_add_user()
    {

        $pritunl = $this->pritunl;

        $organizations = $pritunl->organization();

        $organizationId = $organizations->json()[0]['id'];

        $random = Str::uuid()->toString();

        $addResponse = $pritunl->addUser($organizationId, $random);

        $this->assertStringContainsString($random, $addResponse->body());
    }

    public function test_it_can_delete_user()
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

    public function test_it_can_download(){

        $pritunl = $this->pritunl;

        $organizationId = "6567162cb8915c9dd36bd0c9";

        $userId = "65671630b8915c9dd36bd0d8";

        $downloadResponse = $pritunl->download($organizationId,$userId);

        $this->assertStringContainsString("config.ovpn",$downloadResponse);

    }

    public function test_it_can_get_get_Download_File_Path()
    {
        $pritunl = $this->pritunl;

        $organizationId = "6567162cb8915c9dd36bd0c9";

        $userId = "65671630b8915c9dd36bd0d8";

        $downloadResponse = $pritunl->getDownloadFilePath($organizationId,$userId);

        $this->assertStringContainsString("config.ovpn",$downloadResponse);

        $this->assertStringEndsWith("config.ovpn",$downloadResponse);

    }

    public function test_it_can_download_all_user_configs_by_one_organization()
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


}
