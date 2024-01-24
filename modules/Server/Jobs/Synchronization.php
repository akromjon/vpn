<?php

namespace Modules\Server\Jobs;


use Akromjon\DigitalOceanClient\DigitalOceanClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Server\Models\Enum\CloudProviderType;
use Modules\Server\Models\Server;

class Synchronization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $this->synchronizeWithDigitalOcean();
    }

    private function synchronizeWithDigitalOcean(): void
    {
        $droplets = DigitalOceanClient::connect(config("digitalocean.token"))->droplets();

        foreach ($droplets as $droplet) {

            $server = Server::firstOrNew([
                "uuid" => $droplet["id"],
                "provider" => CloudProviderType::DIGITALOCEAN,
            ]);


            $this->fillWithDigitalOcean($server, $droplet)->save();

        }

        Server::setSynchronizationStatus(false);
    }

    private function fillWithDigitalOcean(Server $server, array $droplet): Server
    {
        $ipAddresses = $this->identifyIpAddresses($droplet["networks"]["v4"]);

        $server->fill([
            'name' => $droplet['name'],
            'config' => [
                "region" => $droplet["region"]["slug"],
                "size" => $droplet["size_slug"],
                "image" => $droplet["image"]["id"],
                "project" => $server->config["project"],
                "ssh_keys" => $server->config["ssh_keys"]
            ],
            'status' => $droplet['status'],
            'ip' => $ipAddresses['ip'] ?? null,
            'price' => $droplet['size']['price_monthly'],
        ]);

        return $server;
    }

    private function identifyIpAddresses(array $networks): array
    {
        $publicIpAddress = null;

        foreach ($networks as $network) {

            if ($network["type"] === "public") {

                $publicIpAddress = $network["ip_address"];

            }

        }

        return [
            "ip" => $publicIpAddress,
        ];
    }
}
