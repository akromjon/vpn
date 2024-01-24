<?php

namespace Modules\Pritunl\Jobs;



use Akromjon\Pritunl\Pritunl as PritunlService;
use Akromjon\Telegram\App\Telegram;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Pritunl\Models\Enum\PritunlStatus;
use Modules\Pritunl\Models\Pritunl;

class EnableReverseAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Pritunl $pritunl,protected string $serverIp,protected string $baseServerURL)
    {
    }
    public function handle():void
    {
        try {

            $pritunl = $this->pritunl;

            $pritunl->update([
                'status' => PritunlStatus::ENABLING_REVERSE_ACTION,
                'reverse_value'=>$this->baseServerURL
            ]);

            PritunlService::enableReverseAction($this->serverIp,$this->baseServerURL);

            $pritunl->update([
                'status' => PritunlStatus::ACTIVE,
                'reverse_action_enabled'=>true,
            ]);

        } catch (\Exception $e) {

            $pritunl->update([
                'status' => PritunlStatus::FAILED_REVERSE_ACTION,
                'reverse_action_enabled'=>false,
            ]);

            Log::error($e->getMessage());

            $telegram = Telegram::set(config('telegram.token'));

            $telegram->sendErrorMessage(config('telegram.chat_id'), $e);
        }
    }
}
