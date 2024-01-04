<?php

namespace App\Http\Controllers;


class SettingsController extends Controller
{

    public function getVersion()
    {
        return response()->json([
            'current_version' => config('app.version'),
            'blocked_versions' => config('app.blocked_versions'),
        ]);
    }

}
