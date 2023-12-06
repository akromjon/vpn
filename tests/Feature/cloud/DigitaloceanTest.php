<?php

namespace Tests\Feature\cloud;

use Akromjon\Pritunl\Cloud\Providers\DigitalOcean;
use Tests\TestCase;


class DigitalOceanTest extends TestCase
{
    public function test_it_can_get_droplets()
    {
        $digitalOcean=new DigitalOcean(env("SERVER_TOKEN"));
        $digitalOcean->authenticate();
        $droplets=$digitalOcean->droplet()->getAll();
    }
}
