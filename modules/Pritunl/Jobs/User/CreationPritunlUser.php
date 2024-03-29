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

class CreationPritunlUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected PritunlUser $pritunlUser)
    {
        //
    }
    public function handle():void
    {
        try{

            $pritunlUser=$this->pritunlUser;

            $pritunlUser->update([
                "status"=>PritunlUserStatus::CREATING,
            ]);

            $client=PritunlClient::connect(
                ip: $pritunlUser->pritunl->server->ip,
                port: $pritunlUser->pritunl->port,
                username: $pritunlUser->pritunl->username,
                password: $pritunlUser->pritunl->password
            );

            $user=$client->addUser($pritunlUser->pritunl->organization_id,$pritunlUser->name);

            $pritunlUser->pritunl->update([
                "user_count"=>$pritunlUser->pritunl->user_count+1,
            ]);

            $vpnPath=$client->download($pritunlUser->pritunl->organization_id,$user[0]["id"]);

            $pritunlUser->update([
                "internal_user_id"=>$user[0]["id"],
                "server_ip"=>$pritunlUser->pritunl->server->ip,
                "status"=>PritunlUserStatus::ACTIVE,
                "opt_secret"=>$user[0]["otp_secret"],
                "vpn_config_path"=>$vpnPath,
            ]);


        }catch(\Exception $e){

            Log::error($e->getMessage());

            $telegram = Telegram::set(config('telegram.token'));

            $telegram->sendErrorMessage(config('telegram.chat_id'), $e);

            $pritunlUser->update([
                "status"=>PritunlUserStatus::FAILED_TO_CREATE,
            ]);
        }

    }


}
