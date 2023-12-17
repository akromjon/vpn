<?php

namespace App\Http\Middleware;

use App\Models\Token;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {

        if (!$request->hasHeader('TOKEN')) {

            return response()->json([
                'message' => 'Not found!'
            ], 404);

        }

        $token = Token::where('token', $request->header('TOKEN'))
            ->where('status', 'active')
            ->first();

        if (!$token) {

            return response()->json([
                'message' => 'Not found!'
            ], 404);

        }

        return $next($request);
    }
}
