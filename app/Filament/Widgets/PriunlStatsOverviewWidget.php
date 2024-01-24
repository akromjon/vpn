<?php

namespace App\Filament\Widgets;


use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Pritunl\Models\Enum\PritunlStatus;
use Modules\Pritunl\Models\Enum\PritunlSyncStatus;
use Modules\Pritunl\Models\Pritunl;

class PriunlStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $pritunl = Pritunl::where('status', PritunlStatus::ACTIVE)
            ->where('sync_status', PritunlSyncStatus::SYNCED)
            ->orderBy('online_user_count', 'desc')
            ->get();

        $stats = [];

        foreach ($pritunl as $pritunl) {
            $stats[] = Stat::make($pritunl->server->country, $pritunl->online_user_count)
                ->description("IP: " . $pritunl->server->ip);
        }

        return $stats;
    }
}
