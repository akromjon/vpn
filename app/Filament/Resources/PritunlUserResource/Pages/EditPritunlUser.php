<?php

namespace App\Filament\Resources\PritunlUserResource\Pages;

use App\Filament\Resources\PritunlUserResource;
use App\Jobs\Pritunl\User\DeletionPritunlUser;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPritunlUser extends EditRecord
{
    protected static string $resource = PritunlUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make("Delete")->color('danger')->after(function () {
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
