<?php

namespace Modules\Client\Models;

use Modules\Client\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Pritunl\Models\PritunlUser;

class ClientPritunlUserConnection extends Model
{
    public $timestamps = false;

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function pritunlUser(): BelongsTo
    {
        return $this->belongsTo(PritunlUser::class);
    }

}
