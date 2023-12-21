<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client\Client;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            "All"=>Tab::make()->badge(fn()=>Client::count()),
            "Online"=>Tab::make()->badge(function(){
                return Client::whereHas('connections',function($query){
                    return $query->where('status','connected');
                })->count();
            })->modifyQueryUsing(function(){
                return Client::whereHas('connections',function($query){
                    return $query->where('status','connected');
                });
            })->badgeColor('success'),
        ];
    }
}
