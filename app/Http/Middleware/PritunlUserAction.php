<?php

namespace App\Http\Middleware;

use Modules\Server\Models\Server;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PritunlUserAction
{

    public function handle(Request $request, Closure $next): Response
    {

        if("local"===app()->environment()) {

            return $next($request);

        }

        $token=$request->header("Token");

        if(!$token) {

            return response()->json([
                'message' => 'Token not found!'
            ], 401);

        }

        if($token!==config("app.pritunl.token")) {

            return response()->json([
                'message' => 'Token not valid!'
            ], 401);
        }

        return $next($request);
    }
}
