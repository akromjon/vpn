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
use Illuminate\Support\Str;
use Modules\Pritunl\Jobs\User\Synchronization as UserSynchronization;
use Modules\Pritunl\Models\Enum\PritunlStatus;
use Modules\Pritunl\Models\Pritunl;

class CreateNumberOfUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Pritunl $pritunl, protected int $numberOfUsers)
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

            $client->createNumberOfUsers(
                organizationId: $pritunl->organization_id,
                numberOfUsers: $this->numberOfUsers
            );

            $pritunl->update([
                "status"=>PritunlStatus::ACTIVE,
                "user_count"=>$pritunl->user_count+$this->numberOfUsers,
            ]);

            UserSynchronization::dispatch($pritunl)->delay(now()->addSeconds(5));
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
