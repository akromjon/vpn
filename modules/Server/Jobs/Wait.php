<?php

namespace Modules\Server\Jobs;


use Akromjon\Pritunl\Cloud\SSH\SSH;
use Modules\Client\Models\Client;

use App\Models\Pritunl\Enum\PritunlUserStatus;
use App\Models\Pritunl\PritunlUser;
use App\Models\Server\Enum\ServerStatus;
use Modules\Server\Models\Server;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;

class Wait implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected PritunlUser $pritunlUser, protected Client $client)
    {

    }

    public function handle(): void
    {
        $pritunlUser=$this->pritunlUser;

        $pritunlUser->refresh();

        if($pritunlUser->status==PritunlUserStatus::IN_USE){

            $pritunlUser->update([
                "status" => PritunlUserStatus::ACTIVE
            ]);
        }

        $lastConnection = $this->client->connections->last();

        if (!empty($lastConnection) && $lastConnection->status == 'idle') {

            $lastConnection->update([
                "status" => 'disconnected',
            ]);

        }



    }
}
