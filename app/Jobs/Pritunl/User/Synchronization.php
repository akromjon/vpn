<?php

namespace App\Jobs\Pritunl\User;

use Akromjon\Pritunl\Pritunl as PritunlClient;
use Akromjon\Telegram\App\Telegram;
use App\Models\Pritunl\Enum\PritunlSyncStatus;
use App\Models\Pritunl\Enum\PritunlUserStatus;
use App\Models\Pritunl\Pritunl;
use App\Models\Pritunl\PritunlUser;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Synchronization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Pritunl $pritunl)
    {
        //
    }
    public function handle()
    {
        try {

            $this->pritunl->update([
                "sync_status" => PritunlSyncStatus::SYNCING,
            ]);

            $client = $this->connect();

            $users = $client->users($this->pritunl->organization_id);

            $users = $this->collect($users);

            foreach ($users as $user) {

                $first=$this->firstOrNew($user["id"]);

                $user["vpn_config_path"] = $client->download($this->pritunl->organization_id, $user["id"]);

                $this->fillUser($first, $user)->save();

            }

            $onlineUserCount=$client->onlineUsers($this->pritunl->organization_id);

            $this->pritunl->update([
                "user_count" => count($users),
                "online_user_count" => count($onlineUserCount),
                "status" => PritunlUserStatus::ACTIVE,
            ]);

            $this->pritunl->update([
                "sync_status" => PritunlSyncStatus::SYNCED,
            ]);


        } catch (\Exception $e) {

            $this->pritunl->update([
                "sync_status" => PritunlSyncStatus::FAILED_TO_SYNC,
            ]);

            $telegram = Telegram::set(config('telegram.token'));

            $telegram->sendErrorMessage(config('telegram.chat_id'), $e);

            Log::error($e->getMessage());
        }

    }

    private function collect(array $users): array
    {
        return collect($users)->where('type', 'client')->all();
    }

    private function firstOrNew(string $userId): PritunlUser
    {
        return PritunlUser::firstOrNew([
            "internal_user_id" => $userId,
            "pritunl_id" => $this->pritunl->id,
            "server_ip" => $this->pritunl->server->ip,
        ]);
    }

    private function connect(): PritunlClient
    {

        return PritunlClient::connect(
            ip: $this->pritunl->server->ip,
            port: $this->pritunl->port,
            username: $this->pritunl->username,
            password: $this->pritunl->password
        );
    }

    private function fillUser(PritunlUser $pritunlUser, array $user): PritunlUser
    {
        return $pritunlUser->fill([
            "name" => $user["name"],
            "is_online" => $user["status"],
            "last_active" => Carbon::createFromTimestamp($user["last_active"])->toDateTimeString(),
            "opt_secret" => $user["otp_secret"],
            "disabled" => $user["disabled"],
            "status" => PritunlUserStatus::ACTIVE,
            "vpn_config_path" => $user["vpn_config_path"],
        ]);
    }
}
