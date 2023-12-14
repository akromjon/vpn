<?php

namespace App\Listeners;

use Akromjon\DigitalOceanClient\DigitalOceanClient;
use App\Enum\CloudProviderTypeEnum;
use App\Enum\ServerEnum;
use App\Events\CreateServerEvent;
use App\Models\Server\Server;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateServerListener implements ShouldQueue
{
    public function handle(CreateServerEvent $event): void
    {
        $server=$event->getServer();

        if($server->cloud_provider_type==CloudProviderTypeEnum::DigitalOcean){

            $this->digitalOceanClient($server);

        }

    }

    private function digitalOceanClient(Server $server):void
    {


        $client=DigitalOceanClient::connect(config("digitalocean.token"));

        try{

            $droplet=$client->createDroplet(
                name: $server->name,
                regionSlug: $server->region,
                sizeSlug: $server->size,
                imageIdOrSlug: $server->image_id,
                projectId: $server->project_id,
                sshKeyIds: $server->ssh_key_ids,
            );

            $server->status=$droplet["status"];

            $server->uuid=$droplet["id"];

            $server->save();

            sleep(120);

            $droplet=$client->droplet($server->uuid);

            $server->fillWithDigitalOcean($server,$droplet)->save();
        }
        catch(\Exception $e){

            if(422===$e->getCode()){

                $server->status=ServerEnum::UNAVAILABLE;

                $server->save();

            }

        }

    }
}
