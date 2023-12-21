<?php

namespace App\Jobs\Server;

use Akromjon\DigitalOceanClient\DigitalOceanClient;
use App\Models\Server\Enum\CloudProviderType;
use App\Models\Server\Enum\ServerStatus;
use App\Models\Server\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Creation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Server $server)
    {
        //
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

        try{


            $droplet=$client->createDroplet(
                name: $server->name,
                regionSlug: $server->config['region'],
                sizeSlug: $server->config['size'],
                imageIdOrSlug: $server->config['image'],
                projectId: $server->config['project'],
                sshKeyIds: $server->config['ssh_keys'],
            );

            $server->status=$droplet["status"];

            $server->uuid=$droplet["id"];

            $server->save();

            sleep(90);

            Synchronization::dispatch();

        }
        catch(\Exception $e){

            if(422===$e->getCode()){

                $server->status=ServerStatus::UNAVAILABLE;

                $server->save();

            }

        }

    }
}
