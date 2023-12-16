<?php

namespace App\Models\Pritunl;

use App\Models\Pritunl\Enum\PritunlUserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PritunlUser extends Model
{
    use HasFactory;

    protected $casts=[
        "status"=>PritunlUserStatus::class,
    ];

    public function pritunl(): BelongsTo
    {
        return $this->belongsTo(Pritunl::class)->with("server");
    }

}
