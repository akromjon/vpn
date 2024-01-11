<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VersionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $osType=$request->header('Os-Type');

        if (!$osType) {

            return response()->json(['message' => 'No Os-Type has been found in your request header!'], 401);

        }

        if (!in_array($osType, ['android', 'ios'])) {

            return response()->json([
                'message' => 'Os-Type is not valid and it must be android or ios'
            ], 401);
        }

        $version=$request->header('Version');

        logger($version);

        if (!$version) {

            return response()->json([
                'message' => 'No version has been found in your request header!'
            ], 401);

        }

        $blockedVersions = config('app.blocked_versions');

        if (in_array($version, $blockedVersions[$osType])) {

            return response()->json([
                'message' => 'You need to update the app!'
            ], 401);

        }

        $osVersions = config('app.os-versions');

        if ($version != $osVersions[$osType]) {

            return response()->json([
                'message' => 'Version mismatch!'
            ], 401);

        }

        return $next($request);
    }
}
