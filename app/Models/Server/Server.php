<?php

namespace App\Models\Server;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Server\Enum\CloudProviderType;
use App\Models\Server\Enum\ServerStatus;
use Illuminate\Support\Facades\Cache;

class Server extends Model
{
    use HasFactory;
    protected $casts=[
        "server_created_at"=>"datetime",
        "status"=>ServerStatus::class,
        "cloud_provider_type"=>CloudProviderType::class,
        "ssh_key_ids"=>"array",
    ];

    public static function getSynchronizationStatus(): bool
    {
        return Cache::get("server_synchronization", false);
    }

    public static function setSynchronizationStatus(bool $status): void
    {
        Cache::put("server_synchronization", $status, now()->addDays());
    }
}
