<?php

namespace App\Http\Controllers;

use App\Exceptions\ClientNotFoundException;
use App\Jobs\ClientLogAction;
use App\Models\Client\Client;
use App\Models\Client\Enum\ClientAction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function act(string $clientUuid,ClientAction $clientAction):void
    {
        $this->cacheClientIfNotCached($clientUuid);

        ClientLogAction::dispatch($clientUuid, request()->ip(), $clientAction);
    }

    private function cacheClientIfNotCached(string $clientUuid):void
    {
        if(!Client::isCached($clientUuid)){

            $client=Client::where('uuid',$clientUuid)->first();

            if(!$client){
                throw new ClientNotFoundException("Client not found");
            }

            Client::setCache($clientUuid);
        }
    }
}
