<?php

namespace Tests\Feature\Jobs;

use App\Jobs\Pritunl\InternalServerOperation;
use App\Models\Pritunl\Enum\InternalServerStatus;
use App\Models\Pritunl\Enum\PritunlStatus;
use App\Models\Pritunl\Pritunl;

class InternalServerOperationTest extends \Tests\TestCase
{
    protected Pritunl $pritunlModel;
    public function setUp(): void
    {
        parent::setUp();

        $this->pritunlModel = Pritunl::where(function ($query) {
            return $query = $query->where("status", PritunlStatus::ACTIVE)
                ->where("internal_server_status", InternalServerStatus::ONLINE);
        })->first();
    }
    public function test_it_can_stop_and_start_restart_online_pritunl()
    {
        $this->it_can_stop_pritunl();
        $this->it_can_start_pritunl();
        $this->it_can_restart_pritunl();
    }

    public function it_can_restart_pritunl()
    {
        $pritunl = $this->pritunlModel->fresh();

        $this->assertEquals($pritunl->internal_server_status, InternalServerStatus::ONLINE);

        $this->assertEquals($pritunl->status, PritunlStatus::ACTIVE);

        InternalServerOperation::dispatchSync($pritunl, "restart");

        $pritunl = $pritunl->fresh();

        $this->assertEquals($pritunl->internal_server_status, InternalServerStatus::ONLINE);

        $this->assertEquals($pritunl->status, PritunlStatus::ACTIVE);
    }

    public function it_can_stop_pritunl()
    {
        $pritunl = $this->pritunlModel->fresh();

        $this->assertEquals($pritunl->internal_server_status, InternalServerStatus::ONLINE);

        $this->assertEquals($pritunl->status, PritunlStatus::ACTIVE);

        InternalServerOperation::dispatchSync($pritunl, "stop");

        $pritunl = $pritunl->fresh();

        $this->assertEquals($pritunl->internal_server_status, InternalServerStatus::OFFLINE);

        $this->assertEquals($pritunl->status, PritunlStatus::ACTIVE);
    }

    public function it_can_start_pritunl()
    {
        $pritunl = $this->pritunlModel->fresh();

        $this->assertEquals($pritunl->internal_server_status, InternalServerStatus::OFFLINE);

        $this->assertEquals($pritunl->status, PritunlStatus::ACTIVE);

        InternalServerOperation::dispatchSync($pritunl, "start");

        $pritunl = $pritunl->fresh();

        $this->assertEquals($pritunl->internal_server_status, InternalServerStatus::ONLINE);

        $this->assertEquals($pritunl->status, PritunlStatus::ACTIVE);
    }
}
