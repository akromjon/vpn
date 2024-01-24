<?php

namespace App\Filament\Resources\PritunlResource\Pages;

use App\Filament\Resources\PritunlResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Modules\Pritunl\Jobs\Deletion;

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
