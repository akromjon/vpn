<?php

namespace App\Filament\Resources\ServerResource\Pages;

use App\Filament\Resources\ServerResource;
use App\Jobs\Server\Creation;
use Filament\Resources\Pages\CreateRecord;

class CreateServer extends CreateRecord
{
    protected static string $resource = ServerResource::class;

    public function getCreatedNotificationTitle(): ?string
    {
        return 'Server creation started and will take some time.';
    }

    protected function AfterCreate()
    {
        Creation::dispatch($this->record);
    }


}
