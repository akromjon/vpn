<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ServerResource;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Enum\ClientAction;

class ClientWidgetBarChart extends ChartWidget
{
    protected static ?string $heading = 'Users';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = "500px";

    protected static string $color = 'success';


    protected function getData(): array
    {

        $clients = DB::table('clients')
            ->join('client_logs', 'clients.id', '=', 'client_logs.client_id')
            ->select('client_logs.country_code', DB::raw('COUNT(DISTINCT clients.id) as client_count'))
            ->groupBy('client_logs.country_code')
            ->orderBy("client_count", "desc")
            ->where('client_logs.action', ClientAction::TOKEN_GENERATED)
            ->get();

        $countries = collect(ServerResource::countries());

        $data = [];

        $clients->collect()->each(function ($row) use ($countries, &$data) {

            $country = ($countries->where('code', $row->country_code)->first());

            $row->country = isset($country['name']) ? $country['name'] : 'unknow_country';

            $data['data'][] = $row->client_count;

            $data['labels'][] = $row->country;
        });


        return [
            'datasets' => [
                [
                    'label' => 'Countries',
                    'data' => $data['data'],
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
