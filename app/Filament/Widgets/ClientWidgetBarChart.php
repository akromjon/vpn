<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ServerResource;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Enum\ClientAction;

class ClientWidgetBarChart extends ChartWidget
{
    protected static ?string $heading = 'Users per Country';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = "500px";

    protected static string $color = 'success';

    protected function getType(): string
    {
        return 'bar';
    }

    public function getData(): array
    {
        $clients = $this->getClientDataFromDB();

        $data = $this->formatClientData($clients);

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

    private function getClientDataFromDB(): Collection
    {
        return DB::table('clients')
            ->join('client_logs', 'clients.id', '=', 'client_logs.client_id')
            ->select('client_logs.country_code', DB::raw('COUNT(DISTINCT clients.id) as client_count'))
            ->where('client_logs.action', ClientAction::TOKEN_GENERATED)
            ->groupBy('client_logs.country_code')
            ->orderByDesc('client_count')
            ->get();
    }

    private function formatClientData(Collection $clients): array
    {
        $data = [
            'data' => [],
            'labels' => [],
        ];

        $countries = $this->getCountries();

        $clients->each(function ($row) use ($countries, &$data) {
            $country = $countries->where('code', $row->country_code)->first();
            $row->country = $country['name'] ?? "Unknown Country";
            $data['data'][] = $row->client_count;
            $data['labels'][] = $row->country;
        });

        return $data;
    }


    private function getCountries(): Collection
    {
        return  collect(ServerResource::countries());
    }
}
