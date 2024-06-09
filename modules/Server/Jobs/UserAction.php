<?php

namespace Modules\Server\Jobs;



use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Client\Models\Client;
use Modules\Pritunl\Models\PritunlUser;
use Modules\Server\Repository\ServerRepository;


class UserAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private mixed $request)
    {
        //
    }

    public function handle(): void
    {
        $request=$this->request;

        $pritunlUser = PritunlUser::where("internal_user_id", $request['pritunl_user_id'])->first();

        if (!$pritunlUser) {

            throw new \Exception("Pritunl user not found");
        }

        $client = Client::where("uuid", $request['client_uuid'])->first();

        if (!$client) {

            throw new \Exception("Client not found");
        }

        // $request['state'] in [connected,disconnected]

        $state=$request['state'];

        $repo=new ServerRepository;

        $action = $repo->$state($client, $pritunlUser->id);

        if (!$action) {

            throw new \Exception("Need to be connected to disconnec");

        }

    }


}
