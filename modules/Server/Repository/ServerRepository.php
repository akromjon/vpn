<?php

namespace Modules\Server\Repository;

use Illuminate\Support\Collection;
use Modules\Server\Models\Enum\ServerStatus;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Client;
use Modules\Pritunl\Models\Enum\InternalServerStatus;
use Modules\Pritunl\Models\Enum\PritunlStatus;
use Modules\Pritunl\Models\Enum\PritunlSyncStatus;
use Modules\Pritunl\Models\Enum\PritunlUserStatus;
use Modules\Pritunl\Models\PritunlUser;
use Illuminate\Support\Facades\File;
use Modules\Server\Jobs\Wait;



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

    public function get(string $ip): object|null
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

    public function downloadConfig(Client $client, string $ip)
    {

        return cache()->remember("{$client->uuid}:{$ip}:pritunl_users", 180, function () use ($client, $ip) {

            $server = $this->get($ip);

            if (!$server) {

                return null;
            }

            $pritunlUser = $this->updateToInUse($server->pritunl_user_id);

            $this->cleanFile($server->vpn_config_path);

            File::append($server->vpn_config_path, PHP_EOL . "setenv UV_CLIENT_UUID {$client->uuid}" . PHP_EOL);

            Wait::dispatch($pritunlUser, $client)->delay(now()->addSeconds(180));

            return $server;
        });
    }

    private function cleanFile(string $filePath)
    {
        $fileContents = File::get($filePath);

        // Split the contents into an array of lines
        $lines = explode(PHP_EOL, $fileContents);

        // Find the index of the line containing "</key>"
        $keyIndex = array_search('</key>', $lines);

        // If the line is found, remove all lines after it
        if ($keyIndex !== false) {
            $lines = array_slice($lines, 0, $keyIndex + 1);
        }

        // Join the remaining lines back into a string
        $newContents = implode(PHP_EOL, $lines);

        // Write the updated contents back to the file
        File::put($filePath, $newContents);
    }

    public function updateToInUse(string $pritunl_user_id): PritunlUser|null
    {
        $pritunlUser = PritunlUser::where("id", $pritunl_user_id)->first();

        if (!$pritunlUser) {

            return null;
        }

        $pritunlUser->update(["status" => PritunlUserStatus::IN_USE]);

        return $pritunlUser;
    }

    public function connected(Client $client, int $pritunlId): bool
    {
        $lastConnection = $client->connections->last();

        if (!empty($lastConnection) && $lastConnection->status == 'connected') {

            $lastConnection->update([
                "status" => 'disconnected',
                'disconnected_at' => now()
            ]);

            $lastConnection = $client->connections->last();
        }

        $client->update(['last_used_at' => now()]);

        $lastConnection = $client->connections()->create([
            "pritunl_user_id" => $pritunlId,
            "status" => 'connected',
            "connected_at" => now(),
        ]);

        $lastConnection->pritunlUser->update([
            "status" => PritunlUserStatus::ACTIVE,
            "is_online" => true,
            'last_active' => now()
        ]);

        return true;
    }

    public function disconnected(Client $client, int $pritunlId = 0):bool
    {
        $lastConnection = $client->connections->last();

        if (!$lastConnection || $lastConnection->status == 'idle') {

            return false;
        }

        $client->update(['last_used_at' => now()]);

        $lastConnection->update([
            "status" => 'disconnected',
            'disconnected_at' => now()
        ]);

        $lastConnection->pritunlUser->update([
            "status" => PritunlUserStatus::ACTIVE,
            "is_online" => false,
            'last_active' => now()
        ]);

        return true;
    }
}
