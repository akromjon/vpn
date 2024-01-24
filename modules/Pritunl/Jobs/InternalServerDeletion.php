<?php

namespace Modules\Pritunl\Jobs;


use Akromjon\Pritunl\Pritunl as PritunlClient;
use Akromjon\Telegram\App\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Pritunl\Models\Enum\InternalServerStatus;
use Modules\Pritunl\Models\Pritunl;

class InternalServerDeletion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Pritunl $pritunl)
    {
    }

    public function handle():void
    {
        try{

            $pritunl=$this->pritunl;

            $pritunl->update([
                "internal_server_status"=>InternalServerStatus::DELETING,
            ]);

            $client=PritunlClient::connect(
                ip: $pritunl->server->ip,
                port: $pritunl->port,
                username: $pritunl->username,
                password: $pritunl->password
            );

            $client->stopServer($pritunl->internal_server_id);

            $client->deleteServer($pritunl->internal_server_id);

            $client->deleteOrganization($pritunl->organization_id);

            $pritunl->update([
                "internal_server_status"=>InternalServerStatus::DELETED,
            ]);

        }catch(\Exception $e){

            Log::error($e->getMessage());

            $telegram = Telegram::set(config('telegram.token'));

            $telegram->sendErrorMessage(config('telegram.chat_id'), $e);

            $pritunl->update([
                "internal_server_status"=>InternalServerStatus::FAILED_TO_DELETE,
            ]);
        }
    }
}
