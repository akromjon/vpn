<?php

namespace App\Http\Controllers;


class SettingsController extends Controller
{

    public function getVersion()
    {

        $osType = request()->header('Os-Type');

        $version = request()->header('Version');

        $osVersions = config('app.os-versions');

        if ($version != $osVersions[$osType]) {

            return response()->json([
                'message' => 'Version mismatch!',
                'your_version'=> $version,
                'code' => 2020,
                'latest_version' => $osVersions,
            ], 400);

        }

        return response()->json([
            "message" => "Version is valid and you can continue!",
            'your_version' => $version,
            'latest_version' => config('app.os-versions'),
        ]);
    }


}
