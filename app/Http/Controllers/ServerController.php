<?php

namespace App\Http\Controllers;

use App\Jobs\ClientLogAction;
use App\Models\Client\Client;
use App\Models\Pritunl\Enum\InternalServerStatus;
use App\Models\Pritunl\Enum\PritunlStatus;
use App\Models\Pritunl\Enum\PritunlSyncStatus;
use App\Models\Server\Enum\ServerStatus;
use Illuminate\Support\Facades\DB;
use App\Models\Client\Enum\ClientAction;
use App\Models\Pritunl\Enum\PritunlUserStatus;

class ServerController extends Controller
{
    public function list()
    {
        if($clientId=request()->get("client_id")){

            $this->act($clientId, ClientAction::LIST_SERVERS);
        }

        $selectColumns = [
            "pritunls.online_user_count as online",
            "servers.ip as ip",
            "servers.flag as flag",
            "servers.name as name",
            "servers.country as country",
            "servers.city as city",
            "servers.country_code as country_code",
        ];

        $results = DB::table("pritunls")
            ->select($selectColumns)
            ->join("servers", "pritunls.server_id", "=", "servers.id")
            ->where("servers.status", ServerStatus::ACTIVE)
            ->where("pritunls.internal_server_status", InternalServerStatus::ONLINE)
            ->where("pritunls.sync_status", PritunlSyncStatus::SYNCED)
            ->where("pritunls.status", PritunlStatus::ACTIVE)
            ->whereColumn("pritunls.online_user_count", "<>", "pritunls.user_count")
            ->orderBy("pritunls.online_user_count")
            ->get();


        return response()->json($results);
    }

    public function connect(string $ip)
    {
        if($clientId=request()->get("client_id")){

            $this->act($clientId, ClientAction::CONNECTING);
        }

        $server = DB::table("servers")
            ->select("pritunl_users.vpn_config_path as vpn_config_path")
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

        if (!$server) {
            return response()->json(["message" => "Server not found"], 404);
        }

        return response()->download($server->vpn_config_path);
    }
}
