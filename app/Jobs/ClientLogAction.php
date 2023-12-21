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

        $client->update([
            'last_used_at'=>now()
        ]);

        if (!$client) {
            return;
        }

        $client->logs()->create([
            'ip_address' => $this->ip ?? '127.0.1.1',
            'action' => $this->action,
        ]);

    }
}
