<?php

use Tests\TestCase;
use Akromjon\Pritunl\Cloud\SSH\SSH;
use Akromjon\Pritunl\Pritunl;

class SSHTest extends TestCase{

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
        $this->ip = env("SERVER_IP");

        $this->username =env("SERVER_USERNAME");

        $this->password =env("SERVER_PASSWORD");

        $this->connectionType=env("SERVER_CONNECTION");

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

    public function test_it_can_get_exception_when_connecting_to_server_kamatera()
    {
        $this->expectException(\Error::class);

        $ssh = $this->ssh;

        $ssh->exec('pwd');

    }
    public function test_it_can_reboot()
    {
        $ssh=$this->ssh;
        $ssh->connect();
        $ssh->exec('reboot');
        $ssh->disconnect();
        $this->assertFalse($ssh->isConntected());

    }

    public function test_it_can_get_error_when_command_is_wrong()
    {
        $ssh=$this->ssh;
        $ssh->connect();
        $ssh->exec('wrong_command');
        $this->assertSame($ssh->getStatusExitCode(),127);
        $ssh->exec("pwd");
        $this->assertSame($ssh->getStatusExitCode(),0);
        $ssh->disconnect();
        $this->assertFalse($ssh->isConntected());

    }
}
