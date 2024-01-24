<?php

namespace App\Http\Middleware;

use Modules\Client\Models\Client;
use Modules\Client\Models\Token;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Closure;
use Illuminate\Support\Facades\DB;

class TokenMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('TOKEN');

        if (!$token) {
            return $this->respondWithTokenError('No Token found!',1000);
        }

        if (!Token::isCached($token)) {

            if (!$this->cacheTokenIfValid($token)) {
                return $this->respondWithTokenError('No active token found!',1005);
            }
        }

        return $next($request);
    }

    private function respondWithTokenError(string $message,int $status=1000): Response
    {
        return response()->json([
            'message' => $message,
            'code' => $status
        ], 401);
    }

    private function cacheTokenIfValid(string $token): bool
    {
        $client = Client::whereHas('token', function ($query) use ($token) {
            $query->where('token', $token);
        })->first();

        if (!$client) {
            return false;
        }

        Token::setCache([$client->token->token => $client->uuid]);

        return true;
    }
}
