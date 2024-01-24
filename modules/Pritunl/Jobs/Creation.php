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
use Modules\Pritunl\Jobs\User\Synchronization;
use Modules\Pritunl\Models\Enum\InternalServerStatus;
use Modules\Pritunl\Models\Enum\PritunlStatus;
use Modules\Pritunl\Models\Pritunl;

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
                ip: $pritunl->server->ip,
                port: $pritunl->port,
                username: $pritunl->username,
                password: $pritunl->password
            );

            $organizationId=$client->addOrganization($pritunl->server->ip)["id"];

            $client->createNumberOfUsers(
                organizationId: $organizationId,
                numberOfUsers: $pritunl->user_count
            );

            sleep(1);

            $serverId=$client->addServer($pritunl->server->ip)["id"];

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

            $telegram = Telegram::set(config('telegram.token'));

            $telegram->sendErrorMessage(config('telegram.chat_id'), $e);

            $pritunl->update([
                "status"=>PritunlStatus::FAILED_TO_CREATE,
            ]);
        }
    }
}
