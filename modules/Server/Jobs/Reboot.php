<?php

namespace Modules\Server\Jobs;


use Akromjon\Pritunl\Cloud\SSH\SSH;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Modules\Server\Models\Enum\ServerStatus;
use Modules\Server\Models\Server;

class Reboot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string $ipAddress,protected string $username="root",protected Server $server)
    {

    }

    public function handle(): void
    {

        $ssh=new SSH(
            ip: $this->ipAddress,
       );

       $ssh->connect();

       $ssh->exec("reboot");

       $ssh->disconnect();

       $this->server->status=ServerStatus::ACTIVE;
       $this->server->save();
    }
}
