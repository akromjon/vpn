<?php

use Tests\TestCase;
use Akromjon\Pritunl\Cloud\SSH\SSH;
class KamateraSSHTest extends TestCase{

    protected SSH $ssh;
    protected string $ip;
    protected string $username;
    protected string $password;
    protected string $connectionType="password";


    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpCredentials();

        $this->ssh = new SSH($this->ip, 22,$this->username,$this->password,$this->connectionType);
    }

    private function setUpCredentials()
    {
        $this->ip = env("TEST_KAMATERA_SERVER_IP");

        $this->username =env("TEST_KAMATERA_USERNAME");

        $this->password =env("TEST_KAMATERA_PASSWORD");
    }

    public function test_it_can_connect_to_server()
    {
        $ssh = $this->ssh;

        $status=$ssh->connect();

        $this->assertTrue($status);
    }

    public function test_it_can_connect_and_do_some_executions_to_server()
    {
        $ssh = $this->ssh;

        $status=$ssh->connect();

        $this->assertTrue($status);

        $result1=$ssh->exec('ls -la');

        $result2=$ssh->exec('pwds');

        $this->assertIsString($result1);

        $this->assertTrue($ssh->isConntected());

        $this->assertIsString($result2);

        $ssh->disconnect();

        $this->assertFalse($ssh->isConntected());
    }

    public function test_it_can_get_exception_when_connecting_to_server()
    {
        $this->expectException(\Error::class);

        $ssh = $this->ssh;

        $ssh->exec('pwds');

    }
}
