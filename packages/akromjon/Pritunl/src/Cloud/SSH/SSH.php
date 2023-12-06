<?php

namespace Akromjon\Pritunl\Cloud\SSH;

use Akromjon\Pritunl\Cloud\SSH\SSHConnection;
use phpseclib3\Net\SSH2;
use Exception;
use Error;

class SSH extends SSHConnection
{
    public function connect():bool|Exception
    {
        $this->SSH2 = new SSH2($this->ip, $this->port);

        if (!$this->login()){

            throw new Exception('Login Failed');

        }

        return true;
    }

    public function exec(string $command):bool|string|Exception|Error
    {
        if($this->isSSH() || !$this->SSH2->isConnected()){

            throw new Exception('SSH2 is not connected');

        }

        return $this->SSH2->exec($command);
    }

    public function disconnect():void
    {
        if ((!$this->isSSH()) && $this->SSH2->isConnected()) {

            $this->SSH2->disconnect();

        }
    }

    public function isConntected():bool
    {
        if(!$this->isSSH()){
            return $this->SSH2->isConnected();
        }

        return false;
    }

    public function setTimeout(int $timeout):void
    {
        if(!$this->isSSH()){
            $this->SSH2->setTimeout($timeout);
        }
    }

    public function getStatusExitCode():int
    {
        if(!$this->isSSH()){
            return $this->SSH2->getExitStatus();
        }

        return 0;
    }

}


