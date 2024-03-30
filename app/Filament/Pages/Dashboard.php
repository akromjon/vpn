<?php

namespace App\Filament\Pages;

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
            ClientLineChartWidget::class,
            ClientWidgetBarChart::class,
            PriunlStatsOverviewWidget::class
        ];
    }

}
