<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KeyMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {

        if(!$request->hasHeader('API-KEY')) {

            return response()->json([
                'message' => 'API_KEY not found'
            ], 404);

        }

        if (config('app.api-key')!=$request->header('API-KEY')) {

            return response()->json([
                'message' => 'Not found!'
            ], 404);

        }

        return $next($request);
    }
}
