<?php

namespace App\Filament\Resources\PritunlResource\Pages;

use App\Filament\Resources\PritunlResource;
use App\Jobs\Pritunl\Creation;
use Filament\Resources\Pages\CreateRecord;

class CreatePritunl extends CreateRecord
{
    protected static string $resource = PritunlResource::class;

    public function getCreatedNotificationTitle(): ?string
    {
        return 'Pritunl creation started and will take some time.';
    }

    protected function AfterCreate()
    {
        Creation::dispatch($this->record);
    }
}
