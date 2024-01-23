<?php

namespace Modules\Client\Models;

use Modules\Client\Models\Client;
use App\Models\Pritunl\PritunlUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
