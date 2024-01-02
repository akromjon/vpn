<?php

namespace App\Jobs\Server;

use Akromjon\DigitalOceanClient\DigitalOceanClient;
use App\Models\Client\Client;
use App\Models\Pritunl\Enum\PritunlUserStatus;
use App\Models\Server\Enum\CloudProviderType;
use App\Models\Server\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Disconnected implements ShouldQueue
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
            "status" => 'disconnected',
            'disconnected_at' => now()
        ]);

        $lastConnection->pritunlUser->update([
            "status" => PritunlUserStatus::ACTIVE,
            "is_online" => false,
            'last_active' => now()
        ]);

        $lastConnection->pritunlUser->pritunl->update([
            "online_user_count" => $lastConnection->pritunlUser->pritunl->online_user_count - 1
        ]);
    }
}
