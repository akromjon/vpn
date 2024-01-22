<?php

namespace App\Jobs;

use App\Models\Client\Client;
use App\Models\Client\Enum\ClientAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stevebauman\Location\Facades\Location;

class ClientLogAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected string $clientUuid, protected string $ip, protected ClientAction $action)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $client = Client::where('uuid',$this->clientUuid)->first();

        if (!$client) {
            throw new \Exception('Client not found!');
        }

        $client->update([
            'last_used_at'=>now()
        ]);

        if ($this->ip === '127.0.0.1') {
            return;
        }

        $location = Location::get($this->ip);

        $client->logs()->create([
            'ip_address' => $this->ip,
            'action' => $this->action,
            'country_code' => $location->countryCode ?? null,
            'region_code' => $location->regionCode ?? null,
            'time_zone' => $location->timezone ?? null,
            'city' => $location->cityName ?? null,
            'latitude' => $location->latitude ?? null,
            'longitude' => $location->longitude ?? null,
        ]);

    }
}
