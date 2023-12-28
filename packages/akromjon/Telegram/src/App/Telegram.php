<?php

namespace Akromjon\Telegram\App;

use Illuminate\Http\Client\Response;

class Telegram extends Base
{

    public static function set(string $token): self
    {
        return new self($token);
    }

    public function getUpdates(): Response
    {
        return $this->method('getUpdates');
    }

    public function sendMessage(string $chat_id, string $message): Response
    {
        if(strlen($message)>4096){

            $message=substr($message,0,4096);
        }

        return $this->method('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $message,
            "parse_mode" => "HTML"
        ]);
    }

    public function sendErrorMessage(string $chat_id, \Exception $e): Response
    {
        $error="<b>Error</b>\n\n";

        $error.="<b>Message:</b> {$e->getMessage()}\n\n";

        $error.="<b>Trace:</b> {$e->getTraceAsString()}\n\n";

        return $this->sendMessage($chat_id, $error);
    }


}
