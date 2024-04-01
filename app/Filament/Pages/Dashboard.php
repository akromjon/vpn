<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PritunlResource\Widgets\PriunlStatusesWidget;
use App\Filament\Resources\ServerResource\Widgets\ServerStatuses;
use App\Filament\Widgets\ClientCountryWidget;
use App\Filament\Widgets\ClientLineChartWidget;
use App\Filament\Widgets\ClientOverviewStatsWidget;
use App\Filament\Widgets\ClientWidgetBarChart;
use App\Filament\Widgets\PriunlStatsOverviewWidget;
use Filament\Pages\Dashboard as PagesDashboard;

class Dashboard extends PagesDashboard
{

    public function getWidgets(): array
    {
        return [
            ClientOverviewStatsWidget::class,
            PriunlStatusesWidget::class,
            ClientLineChartWidget::class,
            ClientWidgetBarChart::class,
        ];
    }

}
