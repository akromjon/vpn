<?php

namespace App\Filament\Widgets;

use Modules\Client\Models\Client;

use App\Models\Pritunl\Enum\PritunlUserStatus;
use App\Models\Pritunl\PritunlUser;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientOverviewStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    protected function getStats(): array
    {
        return [
            Stat::make('Total Clients', Client::count()),
            Stat::make('Online Clients', PritunlUser::where('is_online', true)->count()),
            Stat::make('Free Pritunl Users', PritunlUser::where('is_online', false)->where('status', PritunlUserStatus::ACTIVE)->count()),
        ];
    }
}
