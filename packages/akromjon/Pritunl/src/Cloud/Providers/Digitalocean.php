<?php

namespace Akromjon\Pritunl\Cloud\Providers;

use DigitalOceanV2\Api\Droplet;
use DigitalOceanV2\Client;

class DigitalOcean {

    protected string $token;
    protected Client $client;

    public function __construct(string $token)
    {
        $this->token=$token;
    }

    public function authenticate()
    {
        $this->client = new Client();
        $this->client->authenticate($this->token);
    }

    public function droplet():Droplet
    {
        return $this->client->droplet();
    }
}
