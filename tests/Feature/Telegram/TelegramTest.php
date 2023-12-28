<?php

namespace Tests\Feature\Telegram;

use Akromjon\Telegram\App\Telegram;
use Tests\TestCase;

class TelegramTest extends TestCase
{
    protected string $token;

    protected string $chat_id;

    public function setUp(): void
    {
        parent::setUp();

        $this->token=config('telegram.token');
        $this->chat_id=config('telegram.chat_id');
    }

    public function test_it_returns_telegram_updates()
    {
        $response=Telegram::set($this->token)->getUpdates();
        $this->assertEquals(200,$response->status());
    }

    public function test_it_sends_message_to_telegram()
    {
        $telegram=Telegram::set($this->token);
        $response=$telegram->sendMessage($this->chat_id,'<b>Test message</b>');
        $this->assertEquals(200,$response->status());
    }


}
