<?php

namespace Tests\Feature\Jobs\PritunlUser;

use Akromjon\Pritunl\Pritunl as PritunlClient;
use App\Jobs\Pritunl\User\CreationPritunlUser;
use App\Jobs\Pritunl\User\Synchronization;
use App\Models\Pritunl\Enum\InternalServerStatus;
use App\Models\Pritunl\Enum\PritunlStatus;
use App\Models\Pritunl\Enum\PritunlUserStatus;
use App\Models\Pritunl\Pritunl;
use App\Models\Pritunl\PritunlUser;

class PritunlUserSynchronizationTest extends \Tests\TestCase
{

    public function test_it_can_synchronize_pritunl_user()
    {
        $pritunl=Pritunl::where("status",PritunlStatus::ACTIVE)
                ->where("internal_server_status",InternalServerStatus::ONLINE)
                ->first();

        Synchronization::dispatchSync($pritunl);

        $pritunlUsersCount=PritunlUser::where("pritunl_id",$pritunl->id)->count();

        $pritunlClientUsersCount=count(PritunlClient::connect(
            ip: $pritunl->server->public_ip_address,
            username: $pritunl->username,
            password: $pritunl->password
        )->users($pritunl->organization_id))-1;

        $this->assertGreaterThan(0,$pritunlUsersCount);

        $this->assertEquals($pritunlUsersCount,$pritunlClientUsersCount);

    }

}
