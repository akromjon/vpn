<?php

namespace App\Filament\Resources\PritunlUserResource\Pages;

use App\Filament\Resources\PritunlUserResource;
use App\Models\Pritunl\Enum\PritunlStatus;
use App\Models\Pritunl\Enum\PritunlUserStatus;
use App\Models\Pritunl\PritunlUser;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;


class ListPritunlUsers extends ListRecords
{
    protected static string $resource = PritunlUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            "All"=>Tab::make()->badge(fn()=>PritunlUser::count()),
            "Active"=>Tab::make()->badge(fn()=>PritunlUser::where('status',PritunlUserStatus::ACTIVE)->count())
            ->modifyQueryUsing(function(){
                return PritunlUser::where('status',PritunlUserStatus::ACTIVE);
            }),
            "In Use"=>Tab::make()->badge(fn()=>PritunlUser::where('status',PritunlUserStatus::IN_USE)->count())
            ->modifyQueryUsing(function(){
                return PritunlUser::where('status',PritunlUserStatus::IN_USE);
            }),

            "Online"=>Tab::make()->badge(fn()=>PritunlUser::where('is_online',true)->count())
            ->modifyQueryUsing(function(){
                return PritunlUser::where('is_online',true);
            }),
        ];
    }


}
