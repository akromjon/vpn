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
    public function test_it_can_create_a_user_on_pritunl_with_job()
    {
        $pritunl=Pritunl::where("status",PritunlStatus::ACTIVE)
                ->where("internal_server_status",InternalServerStatus::ONLINE)
                ->first();
        $pritunlUser=PritunlUser::create([
            'name'=>'test',
            'pritunl_id'=>$pritunl->id,
        ]);

        CreationPritunlUser::dispatchSync($pritunlUser);
    }
}
