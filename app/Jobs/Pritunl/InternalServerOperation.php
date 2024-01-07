<?php

namespace App\Jobs\Pritunl;

use Akromjon\Pritunl\Pritunl as PritunlClient;
use Akromjon\Telegram\App\Telegram;
use App\Models\Pritunl\Enum\InternalServerStatus;
use App\Models\Pritunl\Enum\PritunlStatus;
use App\Models\Pritunl\Pritunl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InternalServerOperation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Pritunl $pritunl, protected string $operation)
    {
        //
    }

    public function handle(): void
    {
        try {

            $pritunl = $this->pritunl;

            $pritunl->update([
                "internal_server_status" =>$this->ing($this->operation),
            ]);

            $pritunlClient = PritunlClient::connect(
                ip: $pritunl->server->ip,
                username: $pritunl->username,
                password: $pritunl->password
            );

            $pritunlClient->{$this->operation . "Server"}($pritunl->internal_server_id);

            $pritunl->update([
                "internal_server_status" => $this->ed($this->operation),
            ]);

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            $telegram = Telegram::set(config('telegram.token'));

            $telegram->sendErrorMessage(config('telegram.chat_id'), $e);

            $this->failed($this->operation);
        }


    }

    private function ing(string $operation):?InternalServerStatus
    {
        return match ($operation) {
            "start" => InternalServerStatus::STARTING,
            "stop" => InternalServerStatus::STOPPING,
            "restart" => InternalServerStatus::RESTARTING,
            default =>null,
        };
    }

    private function ed(string $operation):?InternalServerStatus
    {
        return match ($operation) {
            "start" => InternalServerStatus::ONLINE,
            "stop" => InternalServerStatus::OFFLINE,
            "restart" => InternalServerStatus::ONLINE,
            default =>null,
        };
    }

    private function failed(string $operation):?InternalServerStatus
    {
        return match ($operation) {
            "start" => InternalServerStatus::FAILED_TO_START,
            "stop" => InternalServerStatus::FAILED_TO_STOP,
            "restart" => InternalServerStatus::FAILED_TO_RESTART,
            default =>null,
        };
    }
}
