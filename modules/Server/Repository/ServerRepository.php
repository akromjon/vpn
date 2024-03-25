<?php

namespace Modules\Server\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Server\Models\Enum\ServerStatus;
use Illuminate\Support\Facades\DB;
use Modules\Pritunl\Models\Enum\InternalServerStatus;
use Modules\Pritunl\Models\Enum\PritunlStatus;
use Modules\Pritunl\Models\Enum\PritunlSyncStatus;
use Modules\Pritunl\Models\Enum\PritunlUserStatus;
use Modules\Server\Models\Server;

class ServerRepository
{
    public function list(): Collection
    {
        return DB::table("pritunls")
            ->select([
                "pritunls.online_user_count as online",
                "servers.ip as ip",
                "servers.flag as flag",
                "servers.name as name",
                "servers.country as country",
                "servers.city as city",
                "servers.country_code as country_code",
            ])
            ->join("servers", "pritunls.server_id", "=", "servers.id")
            ->where("servers.status", ServerStatus::ACTIVE)
            ->where("pritunls.internal_server_status", InternalServerStatus::ONLINE)
            ->where("pritunls.sync_status", PritunlSyncStatus::SYNCED)
            ->where("pritunls.status", PritunlStatus::ACTIVE)
            ->whereColumn("pritunls.online_user_count", "<>", "pritunls.user_count")
            ->orderBy("pritunls.online_user_count")
            ->get();
    }

    public function get(string $ip): object|static|null
    {
        return DB::table("servers")
            ->select("pritunl_users.vpn_config_path as vpn_config_path", "pritunl_users.id as pritunl_user_id")
            ->join("pritunls", "pritunls.server_id", "=", "servers.id")
            ->join("pritunl_users", "pritunl_users.pritunl_id", "=", "pritunls.id")
            ->where("servers.status", ServerStatus::ACTIVE)
            ->where("pritunls.internal_server_status", InternalServerStatus::ONLINE)
            ->where("pritunls.sync_status", PritunlSyncStatus::SYNCED)
            ->where("pritunls.status", PritunlStatus::ACTIVE)
            ->where("pritunl_users.server_ip", $ip)
            ->where("pritunl_users.is_online", false)
            ->where("pritunl_users.disabled", false)
            ->where("pritunl_users.status", PritunlUserStatus::ACTIVE)
            ->where("servers.ip", $ip)
            ->first();
    }
}
