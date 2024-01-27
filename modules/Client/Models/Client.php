<?php

namespace Modules\Client\Models;

use Modules\Client\Models\ClientPritunlUserConnection;
use Modules\Client\Models\Token;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;

    protected $casts = [
        'device_info' => 'array',
        'monetization_type' => 'array',
        'ad_type'=>'array',
    ];

    public function token(): HasOne
    {
        return $this->hasOne(Token::class);
    }

    public function generateToken(): Token
    {

        if ($this->token()->exists()) {
            $this->token()->delete();
        }


        return $this->token()->create([
            'token' => Str::random(32)
        ]);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ClientLog::class)->orderBy('created_at', 'desc');
    }

    public function connections(): HasMany
    {
        return $this->hasMany(ClientPritunlUserConnection::class, 'client_id', 'id');
    }

    public function currentConnection(): ?ClientPritunlUserConnection
    {
        return $this->connections()->orderBy('id', 'desc')->first();
    }

    public static function isCached(string $uuid): bool
    {
        return in_array($uuid, self::getCache());
    }

    public static function setCache(string $uuid): void
    {
        cache()->forever('clients', array_merge(self::getCache(), [$uuid]));
    }


    public static function getCache(): array
    {
        return cache()->get('clients', []);
    }

    public function totalLogs(): int
    {
        return $this->logs()->count();
    }


}
