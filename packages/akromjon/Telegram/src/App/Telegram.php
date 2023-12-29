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

        logger($message);

        return $this->method('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $message,
            "parse_mode" => "HTML"
        ]);
    }

    public function sendErrorMessage(string $chat_id, \Exception $e): Response
    {

        $error="_____________________________\n\n";

        $app_url=config('app.url');

        $error.="<b>Project: {$app_url}</b>\n\n";

        $error.="<b>Message:</b> {$e->getMessage()}\n\n";

        $traces=$e->getTrace();

        $error.="-------Traces-----------\n\n";

        foreach($traces as $trace)
        {
            if(!isset($trace['file']) || !isset($trace['line'])){
                continue;
            }

            $trace['file']=str_replace(base_path(), '', $trace['file']);

            $error.="File: {$trace['file']}\n";

            $error.="Line: {$trace['line']}\n";
        }

        $error.="-------End-of-Traces-----\n\n";

        return $this->sendMessage($chat_id, $error);
    }


}
