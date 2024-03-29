<?php

namespace Modules\Pritunl\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Server\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Pritunl\Models\Enum\InternalServerStatus;
use Modules\Pritunl\Models\Enum\PritunlStatus;
use Modules\Pritunl\Models\Enum\PritunlSyncStatus;

class Pritunl extends Model
{
    use HasFactory;

    protected $casts=[
        "status"=>PritunlStatus::class,
        "internal_server_status"=>InternalServerStatus::class,
        "sync_status"=>PritunlSyncStatus::class,
        "online_user_count"=>"integer",
    ];

    // we need to do something before we create a new record
    protected static function booted()
    {
        static::creating(function(Pritunl $pritunl){
            $pritunl->uuid=Str::uuid()->toString();
        });
    }

    public function server():BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function users():HasMany
    {
        return $this->hasMany(PritunlUser::class);
    }
}
