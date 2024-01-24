<?php

namespace App\Filament\Resources\PritunlUserResource\Pages;

use App\Filament\Resources\PritunlUserResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Modules\Pritunl\Jobs\User\DeletionPritunlUser;

class EditPritunlUser extends EditRecord
{
    protected static string $resource = PritunlUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make("Delete")->icon("heroicon-o-trash")->color('danger')->after(function () {
                DeletionPritunlUser::dispatch($this->record);
                Notification::make()
                    ->title('Pritunl User Deleted')
                    ->success()
                    ->duration(5000)
                    ->send();
                return redirect(PritunlUserResource::getUrl());
            }),
        ];
    }
}
