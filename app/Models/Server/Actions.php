<?php

namespace App\Models\Server;

use Akromjon\DigitalOceanClient\DigitalOceanClient;
use App\Enum\CloudProviderTypeEnum;

trait Actions
{
    public static function synchronizeWithDigitalOcean():void
    {
        $droplets=DigitalOceanClient::connect(config("digitalocean.token"))->droplets();

        foreach($droplets as $droplet){

            $server=self::firstOrNew(["uuid"=>$droplet["id"]]);

            if(!$server->exists()){

               self::fillWithDigitalOcean($server,$droplet)->save();

            }

        }
    }

    public static function fillWithDigitalOcean(self $server,array $droplet):self
    {
        $server->fill([
            'name' => $droplet['name'],
            'status' => $droplet['status'],
            'region' => $droplet['region']['slug'],
            "size"=>$droplet["size_slug"],
            'image_id' => $droplet['image']['id'],
            'cloud_provider_type' => CloudProviderTypeEnum::DigitalOcean,
            'public_ip_address' => $droplet['networks']['v4'][0]['ip_address'] ?? null,
            'private_ip_address' => $droplet['networks']['v4'][1]['ip_address'] ?? null,
            'server_created_at' => $droplet['created_at'],
            'price' => $droplet['size']['price_monthly'],
        ]);

        return $server;
    }
}
