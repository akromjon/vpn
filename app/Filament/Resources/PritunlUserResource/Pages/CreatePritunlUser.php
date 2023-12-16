<?php

namespace App\Filament\Resources\PritunlUserResource\Pages;

use App\Filament\Resources\PritunlUserResource;
use App\Jobs\Pritunl\User\CreationPritunlUser;
use Filament\Resources\Pages\CreateRecord;

class CreatePritunlUser extends CreateRecord
{
    protected static string $resource = PritunlUserResource::class;

    protected function AfterCreate()
    {
        CreationPritunlUser::dispatch($this->record);
    }
}
