<?php

namespace App\Filament\Resources\PritunlResource\Pages;

use App\Filament\Resources\PritunlResource;
use App\Jobs\Pritunl\Deletion;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPritunl extends EditRecord
{
    protected static string $resource = PritunlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make("Delete")->color("danger")->requiresConfirmation()->icon("heroicon-o-trash")->after(function () {
                Deletion::dispatch($this->record);
                return redirect(PritunlResource::getUrl());
            }),
        ];
    }
}
