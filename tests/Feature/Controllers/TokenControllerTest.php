<?php

namespace Tests\Feature\Controllers;

class TokenControllerTest extends \Tests\TestCase
{
    public function test_it_can_generate_a_token()
    {
        $response = $this->post('api/token', [], [
            'API-KEY' => "wrong-key"
        ]);

        $this->assertEquals(404, $response->getStatusCode());

        $response = $this->post('api/token', [], [
            'API-KEY' => config('app.api-key')
        ]);

        $this->assertEquals(200, $response->status());

        $this->assertStringContainsString('token', $response->getContent());

    }
}
