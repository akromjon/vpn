<?php

namespace Modules\Pritunl\Jobs\User;

use Akromjon\Pritunl\Pritunl as PritunlClient;
use Akromjon\Telegram\App\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Pritunl\Models\Enum\PritunlUserStatus;
use Modules\Pritunl\Models\PritunlUser;

class DeletionPritunlUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected PritunlUser $pritunlUser)
    {
        //
    }

    public function handle(): void
    {
        try {

            $pritunlUser = $this->pritunlUser;

            $pritunlUser->update([
                "status" => PritunlUserStatus::DELETING,
            ]);

            $pritunlClient = PritunlClient::connect(
                ip: $pritunlUser->pritunl->server->ip,
                port: $pritunlUser->pritunl->port,
                username: $pritunlUser->pritunl->username,
                password: $pritunlUser->pritunl->password
            );

            $pritunlUser->pritunl->update([
                "user_count" => $pritunlUser->pritunl->user_count - 1,
            ]);

            $pritunlClient->deleteUser($pritunlUser->pritunl->organization_id, $pritunlUser->internal_user_id);

            $pritunlUser->update([
                "status" => PritunlUserStatus::DELETED,
            ]);

            $pritunlUser->delete();

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            $telegram = Telegram::set(config('telegram.token'));

            $telegram->sendErrorMessage(config('telegram.chat_id'), $e);



            $pritunlUser->update([
                "status" => PritunlUserStatus::FAILED_TO_DELETE,
            ]);
        }


    }


}
