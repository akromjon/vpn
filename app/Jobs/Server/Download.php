<?php

namespace App\Jobs\Server;

use App\Models\Client\Client;
use App\Models\Pritunl\Enum\PritunlUserStatus;
use App\Models\Pritunl\PritunlUser;
use App\Models\Server\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Download implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct(protected int $pritunlUserId, protected Client $client)
    {

    }

    public function handle(): void
    {
        $pritunlUser = PritunlUser::where("id", $this->pritunlUserId)->first();

        $pritunlUser->update(["status" => PritunlUserStatus::IN_USE]);

        $client=$this->client;

        $lastConnection=$client->connections->last();

        if(isset($lastConnection->status) && $lastConnection->status!=='disconnected'){

            $lastConnection->update([
                "status" => 'disconnected',
                'disconnected_at' => now()
            ]);

            $count=$lastConnection->pritunlUser->pritunl->online_user_count - 1;

            $lastConnection->pritunlUser->pritunl->update([
                "online_user_count" => $count < 0 ? 0 : $count
            ]);
        }

        $client->connections()->create([
            "pritunl_user_id" => $pritunlUser->id,
            "status" => 'idle',
        ]);

    }


}
