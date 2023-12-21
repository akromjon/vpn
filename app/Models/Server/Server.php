<?php

namespace App\Models\Server;

use App\Jobs\Server\Creation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Server\Enum\CloudProviderType;
use App\Models\Server\Enum\ServerStatus;
use Illuminate\Support\Facades\Cache;

class Server extends Model
{
    use HasFactory;
    protected $casts=[
        "status"=>ServerStatus::class,
        "provider"=>CloudProviderType::class,
        "config"=>"array",
        "localization"=>"json",
    ];

    public static function getSynchronizationStatus(): bool
    {
        return Cache::get("server_synchronization", false);
    }

    public static function setSynchronizationStatus(bool $status): void
    {
        Cache::put("server_synchronization", $status, now()->addDays());
    }

    public static function addServer(self $self)
    {
        Creation::dispatch($self);
    }
}
