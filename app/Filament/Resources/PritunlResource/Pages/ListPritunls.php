<?php

namespace App\Filament\Resources\PritunlResource\Pages;

use App\Filament\Resources\PritunlResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPritunls extends ListRecords
{
    protected static string $resource = PritunlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Reload')->color("warning")->icon("heroicon-o-magnifying-glass-circle")->action(function(){
                return redirect()->back()->getTargetUrl();
            }),
            CreateAction::make(),
        ];
    }
}
