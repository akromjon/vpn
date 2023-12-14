<?php

namespace Tests\Feature\pritunl;


use Akromjon\Pritunl\Pritunl;
use Illuminate\Support\Str;
use Tests\TestCase;

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
        $this->ip = env("SERVER_IP");

        $this->username =env("TEST_PRITUNL_USERNAME");

        $this->password =env("TEST_PRITUNL_PASSWORD");
    }

    public function test_it_can_get_organization()
    {
        $pritunl = $this->pritunl;

        $organization=$pritunl->organizations();

        $this->assertIsArray($organization);
    }

    public function test_it_can_add_organization()
    {
        $pritunl = $this->pritunl;

        $organizationName=Str::random(10);

        $organization=$pritunl->addOrganization($organizationName);

        $this->assertIsArray($organization);

    }

    public function test_it_can_delete_organization()
    {
        $pritunl = $this->pritunl;

        $organizations=$pritunl->organizations();

        $totalOrganizations=count($organizations);

        $newOrganization=$pritunl->addOrganization(Str::random(10));

        $this->assertEquals($totalOrganizations+1,count($pritunl->organizations()));

        $pritunl->deleteOrganization($newOrganization['id']);

        $this->assertEquals($totalOrganizations,count($pritunl->organizations()));
    }

    public function test_it_can_delete_all_organizations()
    {
        $pritunl = $this->pritunl;

        $organizations=$pritunl->organizations();

        foreach ($organizations as $organization) {
            $pritunl->deleteOrganization($organization['id']);
        }

        $this->assertEquals(0,count($pritunl->organizations()));
    }

    public function test_it_can_fetch_a_single_organization()
    {
        $pritunl = $this->pritunl;

        $organizations=$pritunl->organizations();

        $firstOrganization=$organizations[0];

        $organization=$pritunl->organization($firstOrganization['id']);

        $this->assertEquals($organization['id'],$firstOrganization['id']);
    }

    public function test_it_can_add_user()
    {
        $pritunl = $this->pritunl;

        $organizations=$pritunl->organizations();

        $firstOrganization=$organizations[0];

        $oldUsers=$pritunl->users($firstOrganization['id']);

        // we need to make sure that there is no user in the organization
        foreach ($oldUsers as $oldUser) {
            $pritunl->deleteUser($firstOrganization['id'],$oldUser['id']);
        }

        $user=$pritunl->addUser($firstOrganization['id'],Str::random(10));

        $newUser=$pritunl->users($firstOrganization['id'])[0];

        $this->assertEquals($user[0]['id'],$newUser['id']);
    }

    public function test_it_can_get_users()
    {
        $pritunl = $this->pritunl;

        $organizations=$pritunl->organizations();

        $firstOrganization=$organizations[0];

        $users=$pritunl->users($firstOrganization['id']);

        $this->assertIsArray($users);
    }

    public function test_it_can_add_server()
    {
        $pritunl=$this->pritunl;
        $servers=$pritunl->servers();
        // we need to delete existing servers
        foreach ($servers as $server) {
            $pritunl->deleteServer($server['id']);
        }

        $pritunl->addServer(Str::random(10));

        $servers=$pritunl->servers();

        $this->assertEquals(1,count($servers));
    }

    public function test_it_can_attach_organization_to_server()
    {
        $pritunl=$this->pritunl;
        $servers=$pritunl->servers();
        $organizations=$pritunl->organizations();
        $server=$servers[0];
        $organization=$organizations[0];
        $pritunl->attachOrganization($server['id'],$organization['id']);
        $servers=$pritunl->servers();
        $this->assertIsArray($servers);
    }

    public function test_it_can_create_n_number_of_users(){
        $pritunl=$this->pritunl;
        $orginizations=$pritunl->organizations();
        $organization=$orginizations[0];
        $users=$pritunl->users($organization['id']);

        foreach ($users as $user) {
            $pritunl->deleteUser($organization['id'],$user['id']);
        }

        $userCount=100;

        $pritunl->createNumberOfUsers($organization['id'],$userCount);

        $users=$pritunl->users($organization['id']);

        $this->assertEquals($userCount,count($users));
    }




}
