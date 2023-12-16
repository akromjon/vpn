<?php

namespace App\Filament\Resources\PritunlUserResource\Pages;

use App\Filament\Resources\PritunlUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPritunlUsers extends ListRecords
{
    protected static string $resource = PritunlUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
