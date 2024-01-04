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
        $client = $this->client;

        $lastConnection = $client->connections->last();

        if ($this->shouldCreateNewConnection($lastConnection)) {

            $this->createNewConnection($client);

            return ;
        }

        if ($this->shouldDisconnect($lastConnection)) {

            $this->disconnect($lastConnection);
        }

    }

    private function shouldCreateNewConnection($lastConnection): bool
    {
        return empty($lastConnection) || $lastConnection->status == 'disconnected';
    }

    private function createNewConnection($client): void
    {
        $client->connections()->create([
            "pritunl_user_id" => $this->pritunlUserId,
            "status" => 'idle',
        ]);
    }

    private function shouldDisconnect($lastConnection): bool
    {
        return $lastConnection->status == 'connected';
    }

    private function disconnect($lastConnection): void
    {
        $lastConnection->update([
            "status" => 'disconnected',
            'disconnected_at' => now()
        ]);

        $count = $lastConnection->pritunlUser->pritunl->online_user_count - 1;

        $lastConnection->pritunlUser->pritunl->update([
            "online_user_count" => $count < 0 ? 0 : $count
        ]);

        $lastConnection->pritunlUser->update([
            "status" => PritunlUserStatus::ACTIVE,
            "is_online" => false,
            'last_active' => now()
        ]);
    }


}
