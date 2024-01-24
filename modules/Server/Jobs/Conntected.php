<?php

namespace Modules\Server\Jobs;

use Modules\Client\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Pritunl\Models\Enum\PritunlUserStatus;

class Conntected implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct(protected Client $client)
    {

    }

    public function handle(): void
    {
        $client=$this->client;

        $lastConnection=$client->connections->last();

        $client->update(['last_used_at' => now()]);

        $lastConnection->update([
            "status" => 'connected',
            'connected_at' => now()
        ]);

        $lastConnection->pritunlUser->update([
            "status" => PritunlUserStatus::ACTIVE,
            "is_online" => true,
            'last_active' => now()
        ]);


    }
}
