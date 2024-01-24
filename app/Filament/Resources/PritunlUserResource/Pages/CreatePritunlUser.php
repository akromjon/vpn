<?php

namespace App\Filament\Resources\PritunlUserResource\Pages;

use App\Filament\Resources\PritunlUserResource;
use Filament\Resources\Pages\CreateRecord;
use Modules\Pritunl\Jobs\User\CreationPritunlUser;

class CreatePritunlUser extends CreateRecord
{
    protected static string $resource = PritunlUserResource::class;

    protected function AfterCreate()
    {
        CreationPritunlUser::dispatch($this->record);
    }
}
