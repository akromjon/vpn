<?php

namespace Akromjon\Pritunl;

use Illuminate\Support\Facades\File;

class Headers
{
    protected static string $path="servers/headers.json";
    private static function path():string
    {
        $path=storage_path(self::$path);

        if(!file_exists($path))
        {
            File::put($path,json_encode([]));
        }

        return $path;
    }

    public static function write(string $ip,string $key,string $value):void
    {
        $headers=json_decode(File::get(self::path()),true);

        $headers[$ip][$key]=$value;

        File::put(self::path(),json_encode($headers));
    }

    public static function read(string $ip,string $key):string|null
    {
        $headers=json_decode(File::get(self::path()),true);

        if(!isset($headers[$ip][$key])){
            return null;
        }

        return $headers[$ip][$key];
    }

    public  static function clean():void{
        File::put(self::path(),json_encode([]));
    }

}
