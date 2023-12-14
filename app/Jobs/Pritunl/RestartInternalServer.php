<?php

namespace App\Jobs\Pritunl;

use Akromjon\Pritunl\Pritunl as PritunlClient;
use App\Models\Pritunl\Enum\InternalServerStatus;
use App\Models\Pritunl\Enum\PritunlStatus;
use App\Models\Pritunl\Pritunl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RestartInternalServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Pritunl $pritunl)
    {
        //
    }

    public function handle(){

        try{

            $pritunl=$this->pritunl;

            $pritunl->update([
                "internal_server_status"=>InternalServerStatus::RESTARTING,
            ]);

            $client=PritunlClient::connect(
                ip: $pritunl->server->public_ip_address,
                username: $pritunl->username,
                password: $pritunl->password
            );

            $client->restartServer($pritunl->internal_server_id);

            $pritunl->update([
                "internal_server_status"=>InternalServerStatus::ONLINE,
            ]);
        }
        catch(\Exception $e){

            Log::error($e->getMessage());

            $this->pritunl->update([
                "internal_server_status"=>InternalServerStatus::FAILED_TO_RESTART,
            ]);
        }
    }
}
