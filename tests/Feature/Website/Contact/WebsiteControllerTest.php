<?php

namespace Tests\Feature\Webiste\Contact;

use Tests\TestCase;

class WebsiteControllerTest extends TestCase
{
    public function test_it_can_fail_with_wrong_data()
    {
        $response = $this->post(
            'api/contact',
            [
            ],
            [
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(422);
    }

    public function test_it_can_pass_with_correct_data()
    {
        $requestData= [
            'name' => 'test',
            'subject' => 'feedback',
            'email' => 'test@gmail.com',
            'message' => 'test',
            'ip_address' => '127.0.0.1',
        ];

        $response = $this->post(
            'api/contact',
            $requestData,
            [
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(200);
    }
}
