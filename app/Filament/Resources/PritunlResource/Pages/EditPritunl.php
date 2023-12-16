<?php

namespace App\Filament\Resources\PritunlResource\Pages;

use App\Filament\Resources\PritunlResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPritunl extends EditRecord
{
    protected static string $resource = PritunlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
