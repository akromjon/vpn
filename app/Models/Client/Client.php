<?php

namespace App\Models\Client;

use App\Models\Token;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;

    protected $casts=[
        'device_info'=>'array'
    ];

    public function token():HasMany
    {
        return $this->hasMany(Token::class);
    }

    public function generateToken(): Token
    {
        return $this->token()->create([
            'token'=>Str::random(32)
        ]);
    }

    public function logs():HasMany
    {
        return $this->hasMany(ClientLog::class);
    }

    public static function isCached(string $uuid):bool
    {
        return in_array($uuid,self::getCache());
    }

    public static function setCache(string $uuid):void
    {
        cache()->forever('clients',array_merge(self::getCache(),[$uuid]));
    }

    public static function getCache():array
    {
        return cache()->get('clients',[]);
    }


}
