<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Token extends Model
{
    use HasFactory;

    public static function setCache(string $token):void
    {
        cache()->forever('tokens',array_merge(self::getCache(),[$token]));
    }

    public static function getCache():array
    {
        return cache()->get('tokens',[]);
    }

    public static function isCached(string $token):bool
    {
        return in_array($token,self::getCache());
    }


}
