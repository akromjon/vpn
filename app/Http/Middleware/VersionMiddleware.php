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
        $version = $request->header('VERSION');

        if (!$version) {
            return response()->json(['message' => 'No version has been found in your request header!'], 404);
        }

        $blockedVersions = config('app.blocked_versions');

        if(in_array($version, $blockedVersions))
        {
            return response()->json(['message' => 'You need to update the app!'], 400);
        }

        if ($version != config('app.version')) {
            return response()->json(['message' => 'Version mismatch!'], 400);
        }

        return $next($request);
    }
}
