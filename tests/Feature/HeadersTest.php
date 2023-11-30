<?php

namespace Tests\Feature;

use Akromjon\Pritunl\Headers;
use Tests\TestCase;

class HeadersTest extends TestCase
{
    public function test_it_writes_to_header_json_file()
    {
        Headers::write('192.888.0.1','csrf-token','123456789111');
        $this->assertEquals('123456789111',Headers::read('192.888.0.1','csrf-token'));

    }

    public function test_it_returns_null_if_key_not_exist()
    {
        $this->assertNull(Headers::read('192.888.0.12','csrf-token'));
    }

}
