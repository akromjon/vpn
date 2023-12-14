<?php

namespace App\Jobs\Server;

use Akromjon\DigitalOceanClient\DigitalOceanClient;
use App\Enum\CloudProviderTypeEnum;
use App\Models\Server\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Synchronization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $this->synchronizeWithDigitalOcean();
    }

    private function synchronizeWithDigitalOcean():void
    {
        $droplets=DigitalOceanClient::connect(config("digitalocean.token"))->droplets();

        foreach($droplets as $droplet){

            $server=Server::firstOrNew([
                "uuid"=>$droplet["id"],
                "cloud_provider_type"=>CloudProviderTypeEnum::DigitalOcean,
            ]);

            $this->fillWithDigitalOcean($server,$droplet)->save();
        }

        Server::setSynchronizationStatus(false);
    }

    private function fillWithDigitalOcean(Server $server,array $droplet):Server
    {
        $ipAddresses=$this->identifyIpAddresses($droplet["networks"]["v4"]);

        $server->fill([
            'name' => $droplet['name'],
            'status' => $droplet['status'],
            'region' => $droplet['region']['slug'],
            "size"=>$droplet["size_slug"],
            'image_id' => $droplet['image']['id'],
            'cloud_provider_type' => CloudProviderTypeEnum::DigitalOcean,
            'public_ip_address' => $ipAddresses['public_ip_address'] ?? null,
            'private_ip_address' => $ipAddresses['private_ip_address'] ?? null,
            'server_created_at' => $droplet['created_at'],
            'price' => $droplet['size']['price_monthly'],
        ]);

        return $server;
    }

    private function identifyIpAddresses(array $networks):array
    {
        $publicIpAddress=null;

        $privateIpAddress=null;

        foreach($networks as $network){

            if($network["type"]==="public"){

                $publicIpAddress=$network["ip_address"];

            }

            if($network["type"]==="private"){

                $privateIpAddress=$network["ip_address"];

            }

        }

        return [
            "public_ip_address"=>$publicIpAddress,
            "private_ip_address"=>$privateIpAddress,
        ];
    }
}
