<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ClientLineChartWidget;
use App\Filament\Widgets\ClientOverviewStatsWidget;
use Filament\Pages\Dashboard as PagesDashboard;

class Dashboard extends PagesDashboard
{

    public function getWidgets(): array
    {
        return [
            ClientOverviewStatsWidget::class,
            ClientLineChartWidget::class,
        ];
    }

}
