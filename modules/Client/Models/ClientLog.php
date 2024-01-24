<?php

namespace Modules\Client\Models;

use Modules\Client\Models\Enum\ClientAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientLog extends Model
{
    use HasFactory;

    protected $casts=[
       "action"=>ClientAction::class,
    ];
}
