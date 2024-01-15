<?php

namespace App\Http\Controllers;

use App\Jobs\ClientLogAction;
use App\Jobs\Server\Conntected;
use App\Jobs\Server\Disconnected;
use App\Jobs\Server\Download;
use App\Jobs\Server\Wait;
use App\Models\Client\Client;
use App\Models\Pritunl\Enum\InternalServerStatus;
use App\Models\Pritunl\Enum\PritunlStatus;
use App\Models\Pritunl\Enum\PritunlSyncStatus;
use App\Models\Pritunl\PritunlUser;
use App\Models\Server\Enum\ServerStatus;
use Illuminate\Support\Facades\DB;
use App\Models\Client\Enum\ClientAction;
use App\Models\Pritunl\Enum\PritunlUserStatus;
use App\Models\Token;

class ServerController extends Controller
{
    public function list()
    {
        $this->act(Token::getCachedClientUuid(), ClientAction::LIST_SERVERS);

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

    public function download(string $ip)
    {

        $server = DB::table("servers")
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

        if (empty($server)) {
            return response()->json(["message" => "Server not found"], 404);
        }

        $this->act(Token::getCachedClientUuid(), ClientAction::DOWNLOADED_CONFIG);

        $pritunlUser = PritunlUser::where("id", $server->pritunl_user_id)->first();

        $pritunlUser->update(["status" => PritunlUserStatus::IN_USE]);

        $client = Token::getClient();

        Download::dispatch($server->pritunl_user_id, $client);

        Wait::dispatch($pritunlUser, $client)->delay(now()->addSeconds(20));

        return response()->download($server->vpn_config_path, 'vpn_config.ovpn');
    }

    public function connected()
    {
        $client = Token::getClient();

        $lastConnection = $client->connections->last();

        if (!$lastConnection) {

            return response()->json(["message" => "Need to be downloaded to connect"], 400);

        }

        if ($lastConnection->status == 'connected') {

            return response()->json(["message" => "Need to be disconnected to connect"], 400);
        }

        if ($lastConnection->status == 'disconnected') {

            return response()->json(["message" => "Need to be downloaded to connect"], 400);
        }

        Conntected::dispatch($client);

        return response()->json(["message" => "Connected"]);
    }

    public function disconnected()
    {
        $client = Token::getClient();

        $lastConnection = $client->connections->last();

        if(!$lastConnection){

            return response()->json(["message" => "Need to be connected to disconnect"], 400);
        }

        if ($lastConnection->status == 'disconnected') {

            return response()->json(["message" => "Need to be connected to disconnect"], 400);
        }

        if ($lastConnection->status == 'idle') {

            return response()->json(["message" => "Need to be connected to disconnect"], 400);

        }

        Disconnected::dispatch($client);

        return response()->json(["message" => "Disconnected"]);
    }

    public function pritunlUserAction(string $action, string $pritunlUserUuid)
    {
        if(!in_array($action, ["connected", "disconnected"])) {

            return response()->json(["message" => "Action not found"], 404);

        }

        $pritunlUser = PritunlUser::where("internal_user_id", $pritunlUserUuid)->first();

        if(!$pritunlUser) {

            return response()->json(["message" => "Pritunl user not found"], 404);

        }

        $identifyAction=$action == "connected" ? true : false;

        if($pritunlUser->is_online == $identifyAction){

            return response()->json([
                "message" => "Pritunl user already {$action}"
            ], 400);
        }

        $pritunlUser->update([
            "status" => PritunlUserStatus::ACTIVE,
            "is_online" => $identifyAction
        ]);

        $count=0;

        if($identifyAction){

            $count = $pritunlUser->pritunl->online_user_count + 1;

        }else{
            $pritunlUser->pritunl->online_user_count - 1 < 0 ? $count = 0 : $count = $pritunlUser->pritunl->online_user_count - 1;
        }

        $pritunlUser->pritunl->update([
            "online_user_count" => $count
        ]);

        return response()->json([
            "status" => "ok"
        ]);
    }


}
