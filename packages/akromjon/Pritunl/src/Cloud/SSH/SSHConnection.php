<?php

namespace Akromjon\Pritunl\Cloud\SSH;

use phpseclib3\Crypt\Common\AsymmetricKey;
use phpseclib3\Crypt\PublicKeyLoader;
use Illuminate\Support\Facades\File;
use phpseclib3\Net\SSH2;
abstract class SSHConnection
{
    protected string $ip;
    protected int $port=22;
    protected string $username;
    protected string $password;
    protected string $connectionType;
    protected SSH2 $SSH2;
    protected string $privateKeyPath;


    public function __construct(string $ip, int $port=22, string $username="root",string $password="",string $connectionType="key" ,string $privateKeyPath="ssh")
    {
        $this->ip = $ip;

        $this->port = $port;

        $this->username = $username;

        $this->password = $password;

        $this->connectionType = $connectionType;

        $this->privateKeyPath = $privateKeyPath;

    }

    protected function getFilePath():string
    {
        return File::get(storage_path($this->privateKeyPath."/id_rsa"));
    }

    protected function getKey():AsymmetricKey
    {
        return PublicKeyLoader::load($this->getFilePath());
    }

    protected function isSSH():bool
    {
        return $this->SSH2===null;
    }

    protected function connectionType():string
    {
        return $this->connectionType;
    }

    protected function login():bool
    {
        if($this->connectionType()=="password"){

            return $this->SSH2->login($this->username, $this->password);

        }

        return $this->SSH2->login($this->username, $this->getKey());
    }

    public static function load(...$params):self
    {
        return new static(...$params);
    }


}


