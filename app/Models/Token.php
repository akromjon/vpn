<?php

namespace App\Models;

use App\Models\Client\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Token extends Model
{
    use HasFactory;

    public function client():BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public static function getClient():?Client
    {
        if(!request()->header('TOKEN')){
            return null;
        }

        $client=self::where('token',request()->header('TOKEN'))->with('client')->first();

        return $client->client;
    }

    public static function getCachedClientUuid():?string
    {
        return self::getCache()[request()->header('TOKEN')];
    }


    public static function setCache(array $token):void
    {
        cache()->forever('client_tokens',array_merge(self::getCache(),$token));
    }

    public static function removeCache(string $token):void
    {
        $cache = self::getCache();
        unset($cache[$token]);
        cache()->forever('client_tokens',$cache);
    }

    public static function getCache():array
    {
        return cache()->get('client_tokens',[]);
    }

    public static function isCached(string $token):bool
    {
        return array_key_exists($token,self::getCache());
    }


}
