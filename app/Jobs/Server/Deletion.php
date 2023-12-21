<?php

namespace App\Jobs\Server;

use Akromjon\DigitalOceanClient\DigitalOceanClient;
use App\Models\Server\Enum\CloudProviderType;
use App\Models\Server\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Deletion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct(protected Server $server)
    {

    }

    public function handle(): void
    {
        $server=$this->server;

        if($server->provider==CloudProviderType::DIGITALOCEAN){

            $this->digitalOceanClient($server);

        }
    }

    private function digitalOceanClient(Server $server):void
    {
        $client=DigitalOceanClient::connect(config("digitalocean.token"));

        $client->deleteDroplet($server->uuid);

        $server->delete();
    }
}
