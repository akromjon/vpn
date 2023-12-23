<?php

namespace App\Filament\Resources\ServerResource\Pages;

use App\Filament\Resources\ServerResource;
use App\Models\Server\Server;
use Filament\Resources\Pages\CreateRecord;

class CreateServer extends CreateRecord
{
    protected static string $resource = ServerResource::class;

    public function getCreatedNotificationTitle(): ?string
    {
        return 'Server creation started and will take some time.';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['provider'] === 'digitalocean') {
            $data = $this->moveDigitalOceanSpecificDataToConfig($data);
        }

        unset($data['image']);

        return $data;
    }

    private function moveDigitalOceanSpecificDataToConfig(array $data): array
    {

        $data['config']['ssh_keys'] = [$data['config']['ssh_keys']];

        return $data;
    }



    protected function AfterCreate()
    {
        Server::addServer($this->record);
    }


}
