<?php

namespace App\Http\Middleware;

use App\Models\Server\Server;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PritunlUserAction
{

    public function handle(Request $request, Closure $next): Response
    {

        $server=Server::where("ip", $request->ip())->first();

        if(!$server) {

            return response()->json([
                'message' => 'Server not found!'
            ], 404);

        }

        return $next($request);
    }
}
