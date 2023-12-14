<?php

namespace App\Models\Server;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\CloudProviderTypeEnum;
use App\Enum\ServerEnum;
use Illuminate\Support\Facades\Cache;

class Server extends Model
{
    use HasFactory;
    protected $casts=[
        "server_created_at"=>"datetime",
        "status"=>ServerEnum::class,
        "cloud_provider_type"=>CloudProviderTypeEnum::class,
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
