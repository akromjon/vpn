<?php

namespace Tests\Feature\Jobs;

use App\Jobs\Pritunl\User\CreationPritunlUser;
use App\Models\Pritunl\Enum\InternalServerStatus;
use App\Models\Pritunl\Enum\PritunlStatus;
use App\Models\Pritunl\Enum\PritunlUserStatus;
use App\Models\Pritunl\Pritunl;
use App\Models\Pritunl\PritunlUser;

class CreationPritunlUserTest extends \Tests\TestCase
{

    public function test_it_can_create_pritunl_user()
    {
        $pritunl=Pritunl::where("status",PritunlStatus::ACTIVE)
            ->where("internal_server_status",InternalServerStatus::ONLINE)
            ->first();


        if(!$pritunl){
            $this->markTestSkipped("No active pritunl found");
        }

        $pritunlUser=PritunlUser::create([
            "pritunl_id"=>$pritunl->id,
            "server_ip"=>$pritunl->server->public_ip_address,
            "name"=>"test",
        ]);

        CreationPritunlUser::dispatchSync($pritunlUser);

        $pritunlUser=$pritunlUser->fresh();

        $this->assertEquals($pritunlUser->status,PritunlUserStatus::ACTIVE);

        $this->assertNotNull($pritunlUser->vpn_config_path);

        $this->assertNotNull($pritunlUser->internal_user_id);

    }

}
