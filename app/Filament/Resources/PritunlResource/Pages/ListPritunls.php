<?php

namespace App\Filament\Resources\PritunlResource\Pages;

use App\Filament\Resources\PritunlResource;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Modules\Pritunl\Models\Enum\PritunlStatus;
use Modules\Pritunl\Models\Pritunl;

class ListPritunls extends ListRecords
{
    protected static string $resource = PritunlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Reload')->color("warning")->icon("heroicon-o-magnifying-glass-circle")->action(function () {
                return redirect()->back()->getTargetUrl();
            }),
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            "Total Active" => Tab::make()->badge(fn() => Pritunl::where('status', PritunlStatus::ACTIVE)->count())->modifyQueryUsing(function () {
                return Pritunl::where('status', PritunlStatus::ACTIVE);
            }),
            "All" => Tab::make()->badge(fn() => Pritunl::count()),
        ];
    }
}
