<?php

namespace App\Models\Pritunl;

use App\Models\Pritunl\Enum\InternalServerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Pritunl\Enum\PritunlStatus;
use App\Models\Server\Enum\ServerStatus;
use App\Models\Server\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pritunl extends Model
{
    use HasFactory;

    protected $casts=[
        "status"=>PritunlStatus::class,
        "internal_server_status"=>InternalServerStatus::class,
    ];

    public function server():BelongsTo
    {
        return $this->belongsTo(Server::class)->where("status",ServerStatus::ACTIVE);
    }
}
