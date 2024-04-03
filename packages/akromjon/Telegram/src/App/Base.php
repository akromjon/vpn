<?php

namespace Akromjon\Telegram\App;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class Base
{

    protected function __construct(protected string $token)
    {
    }
    protected function method(string $method, array $params = []): Response
    {
        $response = Http::get("https://api.telegram.org/bot{$this->token}/{$method}", $params);

        if (200 !== $response->status()) {

            throw new \Exception("Telegram API error: {$response->body()}");
        }

        return $response;
    }

    public function sendFile(string $method = "sendDocument", string $filePath, array $params = []): Response
    {
        $response = Http::timeout(300)->attach(
            'document',
            file_get_contents($filePath),
            basename($filePath)
        )->post("https://api.telegram.org/bot{$this->token}/{$method}", $params);

        if (200 !== $response->status()) {
            throw new \Exception("Telegram API error: {$response->body()}");
        }

        return $response;
    }
}
