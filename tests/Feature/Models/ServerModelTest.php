<?php

namespace Tests\Feature\Models;

use Akromjon\DigitalOceanClient\DigitalOceanClient;
use Modules\Server\Models\Server;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerModelTest extends TestCase
{

    protected DigitalOceanClient $digitalOceanClient;
    protected Server $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->digitalOceanClient=DigitalOceanClient::connect(config("digitalocean.token"));
    }
    public function test_it_can_synchronize_with_digitalocean()
    {
        $droplets=$this->digitalOceanClient->droplets();
        Server::synchronizeWithDigitalOcean();
        $this->assertDatabaseCount("servers",count($droplets));
    }
}
