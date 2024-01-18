<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VersionMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {

        $osType = $request->header('Os-Type');

        if (!$osType) {

            return response()->json([
                'message' => 'No Os-Type has been found in your request header!',
                'code' => 2000
            ], 400);

        }

        if (!in_array($osType, ['android', 'ios'])) {

            return response()->json([
                'message' => 'Os-Type is not valid and it must be android or ios',
                'code' => 2005
            ], 400);
        }

        $version = $request->header('Version');

        if (!$version) {

            return response()->json([
                'message' => 'No version has been found in your request header!',
                'code' => 2010
            ], 400);

        }

        $blockedVersions = config('app.blocked_versions');

        if (in_array($version, $blockedVersions[$osType])) {

            return response()->json([
                'message' => 'You need to update the app!',
                'code' => 2015
            ], 400);

        }

        return $next($request);
    }
}
