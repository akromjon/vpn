<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;

class VersionMiddlewareTest extends TestCase
{

    protected string $token = "NqpZaoKMBjCXpat1va8Urpmh9Jv8bfvs";

    public function test_it_can_fail_with_no_os_type()
    {
        $response = $this->withHeaders([
            'TOKEN' => $this->token,
        ])->json('get', '/api/servers/');

        $this->assertEquals(401, $response->status());

        $this->assertStringContainsString('No Os-Type', $response->getContent());

    }

    public function test_it_can_fail_with_no_version()
    {

        $response = $this->withHeaders([
            'TOKEN' => $this->token,
            "os-type" => "android",
        ])->json('get', '/api/servers/');

        $this->assertStringContainsString('No version has been found in your request header!', $response->getContent());

        $this->assertEquals(401, $response->status());
    }

    public function test_it_can_fail_with_invalid_os_type()
    {

        $response = $this->withHeaders([
            'TOKEN' => $this->token,
            "os-type" => "invalid",
            "version" => "1.0.0"
        ])->json('get', '/api/servers/');

        $this->assertStringContainsString('Os-Type is not valid and it must be android or ios', $response->getContent());

        $this->assertEquals(401, $response->status());
    }

    public function test_it_can_fail_with_invalid_version()
    {
        $response = $this->withHeaders([
            'TOKEN' => $this->token,
            "os-type" => "android",
            "version" => "0.5.0"
        ])->json('get', '/api/servers/');

        $this->assertStringContainsString('You need to update the app!', $response->getContent());

        $this->assertEquals(401, $response->status());
    }

    public function test_it_can_pass_with_valid_version()
    {
        $response = $this->withHeaders([
            'TOKEN' => $this->token,
            "os-type" => "android",
            "version" => "1.0.0"
        ])->json('get', '/api/servers/');

        $this->assertEquals(200, $response->status());
    }
}
