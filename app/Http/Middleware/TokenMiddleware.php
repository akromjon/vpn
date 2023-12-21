<?php

namespace App\Http\Middleware;

use App\Models\Token;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class TokenMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('TOKEN');

        if (!$token) {
            return $this->respondWithTokenError('No Token found!');
        }

        if (!Token::isCached($token)) {

            if (!$this->cacheTokenIfValid($token)) {
                return $this->respondWithTokenError('No active token found!');
            }
        }

        return $next($request);
    }

    private function respondWithTokenError(string $message): Response
    {
        return response()->json(['message' => $message], 404);
    }

    private function cacheTokenIfValid(string $token): bool
    {
        $tokenModel = Token::where('token', $token)->first();

        if (!$tokenModel) {
            return false;
        }

        Token::setCache($tokenModel->token);

        return true;
    }
}
