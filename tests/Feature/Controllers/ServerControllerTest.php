<?php

namespace Tests\Feature\Controllers;

use Illuminate\Support\Facades\File;
use Modules\Pritunl\Models\PritunlUser;

class ServerControllerTest extends \Tests\TestCase
{
    public function test_it_find_and_delete_that_file()
    {

        $filePath = (PritunlUser::first())->vpn_config_path;

        $fileContents = File::get($filePath);

        // Split the contents into an array of lines
        $lines = explode(PHP_EOL, $fileContents);

        // Find the index of the line containing "</key>"
        $keyIndex = array_search('</key>', $lines);

        // If the line is found, remove all lines after it
        if ($keyIndex !== false) {
            $lines = array_slice($lines, 0, $keyIndex + 1);
        }

        // Join the remaining lines back into a string
        $newContents = implode(PHP_EOL, $lines);

        // Write the updated contents back to the file
        File::put($filePath, $newContents);


        File::append($filePath, PHP_EOL."setenv UV_CLIENT_UUID uuuid" . PHP_EOL);


    }
}
