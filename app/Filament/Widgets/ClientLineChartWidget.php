<?php

namespace App\Filament\Widgets;

use App\Models\Client\Client;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ClientLineChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Registered Clients';

    protected static string $color = 'success';

    public ?string $filter = 'week';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = "300px";

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
            'all' => 'All time',
        ];
    }

    protected function getData(): array
    {
        $filterData = $this->getFilterData();

        $data = $this->fetchData($filterData);

        return [
            'datasets' => [
                [
                    'label' => 'Clients Registered',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => Carbon::parse($value->date)->format($this->filter === 'today' ? 'H:i' : 'd.m.Y')),
        ];
    }

    protected function getFilterData(): array
    {
        return [
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
                'per' => 'perHour'
            ],
            'week' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
                'per' => 'perDay'
            ],
            'month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
                'per' => 'perDay'
            ],
            'year' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
                'per' => 'perMonth'
            ],
            'all' => [
                'start' => now()->subYears(1),
                'end' => now(),
                'per' => 'perMonth'
            ],
        ];
    }

    protected function fetchData(array $filterData)
    {
        return Trend::model(Client::class)
                    ->between(
                        start: $filterData[$this->filter]['start'],
                        end: $filterData[$this->filter]['end'],
                    )
            ->{$filterData[$this->filter]['per']}()
                ->count();
    }

    protected function getType(): string
    {
        return 'line';
    }
}
