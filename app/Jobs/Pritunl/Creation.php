<?php

namespace App\Jobs\Pritunl;

use Akromjon\Pritunl\Pritunl as PritunlClient;
use App\Jobs\Pritunl\User\Synchronization;
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

class Creation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Pritunl $pritunl)
    {
        //
    }
    public function handle():void
    {
        try{

            $pritunl=$this->pritunl;

            $pritunl->update([
                "status"=>PritunlStatus::CREATING,
            ]);

            $client=PritunlClient::connect(
                ip: $pritunl->server->public_ip_address,
                username: $pritunl->username,
                password: $pritunl->password
            );

            $organizationId=$client->addOrganization($pritunl->server->public_ip_address)["id"];

            $client->createNumberOfUsers(
                organizationId: $organizationId,
                numberOfUsers: $pritunl->user_count
            );

            sleep(1);

            $serverId=$client->addServer($pritunl->server->public_ip_address)["id"];

            $client->attachOrganization(
                serverId: $serverId,
                organizationId: $organizationId,
            );

            $client->startServer($serverId);

            $pritunl->update([
                "organization_id"=>$organizationId,
                "internal_server_id"=>$serverId,
                "internal_server_status"=>InternalServerStatus::ONLINE,
                "status"=>PritunlStatus::ACTIVE,
                "user_count"=>$pritunl->user_count,
            ]);

            Synchronization::dispatch($pritunl);
        }
        catch(\Exception $e){

            Log::error($e->getMessage());

            $pritunl->update([
                "status"=>PritunlStatus::FAILED_TO_CREATE,
            ]);
        }
    }
}