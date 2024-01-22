<?php

namespace App\Models\Pritunl;

use App\Models\ClientPritunlUserConnection;
use App\Models\Pritunl\Enum\PritunlUserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PritunlUser extends Model
{
    use HasFactory;

    protected $casts = [
        "status" => PritunlUserStatus::class,
    ];
    protected static function booted()
    {
        static::updated(function ($model) {

            if(!$model->isDirty('is_online') ||  $model->getOriginal('is_online') == $model->is_online){

                return;

            }

            $model->is_online ? $model->incrementOnlineUserCount() : $model->decrementOnlineUserCount();

        });

        static::deleting(function ($model) {

            if ($model->is_online) {

                $model->decrementOnlineUserCount();

            }

        });
    }

    public function incrementOnlineUserCount()
    {
        $this->pritunl->increment('online_user_count');
    }

    public function decrementOnlineUserCount()
    {
        $pritunl=$this->pritunl->refresh();
        // keep eye on this
        if ($pritunl->online_user_count > 0) {
            $pritunl->decrement('online_user_count');
        }
    }

    public function pritunl(): BelongsTo
    {
        return $this->belongsTo(Pritunl::class)->with("server");
    }

    public function connections(): HasMany
    {
        return $this->hasMany(ClientPritunlUserConnection::class, 'pritunl_user_id', 'id');
    }

}
