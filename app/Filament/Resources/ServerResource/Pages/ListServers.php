<?php

namespace App\Filament\Resources\ServerResource\Pages;

use App\Filament\Resources\ServerResource;
use App\Jobs\Server\Synchronization;
use App\Models\Server\Server;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListServers extends ListRecords
{
    protected static string $resource = ServerResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('Reload')->color("warning")->icon("heroicon-o-magnifying-glass-circle")->action(function(){
                return redirect()->back()->getTargetUrl();
            }),

            Action::make('Synchronize Servers')->color("success")->icon("heroicon-o-arrow-path")->action(function(){

                Notification::make()
                    ->title('Synchronization started and will take some time.')
                    ->success()
                    ->send();

                Server::setSynchronizationStatus(true);

                Synchronization::dispatch();

            })->disabled(function(){
                return Server::getSynchronizationStatus();
            }),

            CreateAction::make()->successNotification(null),
        ];
    }
}
