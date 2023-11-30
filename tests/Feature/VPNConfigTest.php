<?php

use Tests\TestCase;

use Akromjon\Pritunl\VPNConfig;
class VPNConfigTest extends TestCase
{
    public function test_it_can_get_path()
    {
        $path=VPNConfig::path;

        $this->assertIsString($path);
    }
}
