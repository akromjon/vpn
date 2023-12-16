<?php

namespace App\Jobs\Pritunl\User;

use Akromjon\Pritunl\Pritunl as PritunlClient;
use App\Models\Pritunl\Enum\PritunlUserStatus;
use App\Models\Pritunl\PritunlUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
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
                ip: $pritunlUser->pritunl->server->public_ip_address,
                username: $pritunlUser->pritunl->username,
                password: $pritunlUser->pritunl->password
            );

            $user=$client->addUser($pritunlUser->pritunl->organization_id,$pritunlUser->name);

            $vpnPath=$client->download($pritunlUser->pritunl->organization_id,$user[0]["id"]);

            $pritunlUser->update([
                "internal_user_id"=>$user[0]["id"],
                "server_ip"=>$pritunlUser->pritunl->server->public_ip_address,
                "status"=>PritunlUserStatus::ACTIVE,
                "opt_secret"=>$user[0]["otp_secret"],
                "vpn_config_path"=>$vpnPath,
            ]);


        }catch(\Exception $e){

            Log::error($e->getMessage());

            $pritunlUser->update([
                "status"=>PritunlUserStatus::FAILED_TO_CREATE,
            ]);
        }

    }


}
