<?php

use Tests\TestCase;
use Akromjon\Pritunl\Cloud\SSH\SSH;
class DigitalOceanSSHTest extends TestCase{

    protected SSH $ssh;
    protected string $ip;
    protected string $pubKey;
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpCredentials();

        $this->ssh = new SSH($this->ip, 22,'root');

    }

    private function setUpCredentials()
    {
        $this->ip = env("TEST_SERVER_IP");

        $this->pubKey =env("TEST_SERVER_PUB_KEY");
    }

    public function test_it_can_connect_to_server()
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
