<?php

namespace App\Jobs\Pritunl;

use Akromjon\Pritunl\Pritunl as PritunlClient;
use App\Models\Pritunl\Enum\InternalServerStatus;
use App\Models\Pritunl\Enum\PritunlStatus;
use App\Models\Pritunl\Enum\PritunlSyncStatus;
use App\Models\Pritunl\Pritunl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Deletion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Pritunl $pritunl)
    {
    }
    public function handle():void
    {
        try {

            $pritunl = $this->pritunl;

            $pritunl->update([
                "status" => PritunlStatus::DELETING,
            ]);

            $pritunlClient = PritunlClient::connect(
                ip: $pritunl->server->public_ip_address,
                username: $pritunl->username,
                password: $pritunl->password
            );

            if(!is_null($pritunl->internal_server_id)){

                $pritunlClient->stopServer(
                    serverId: $pritunl->internal_server_id
                );

                $pritunlClient->detachOrganization(
                    serverId: $pritunl->internal_server_id,
                    organizationId:$pritunl->organization_id
                );

                $pritunlClient->deleteServer(
                    serverId: $pritunl->internal_server_id
                );
            }

            if(!is_null($pritunl->organization_id)){

                $pritunlClient->deleteOrganization(
                    organizationId: $pritunl->organization_id
                );

            }


            $pritunl->update([
                "internal_server_status" => InternalServerStatus::DELETED,
                "status" => PritunlStatus::DELETED,
                "sync_status"=>PritunlSyncStatus::NOT_SYNCED,
            ]);

            $pritunl->delete();

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            $pritunl->update([
                "status" => PritunlStatus::FAILED_TO_DELETE,
            ]);
        }
    }
}
