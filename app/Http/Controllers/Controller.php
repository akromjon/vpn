<?php

namespace App\Http\Controllers;

use Modules\Client\Jobs\ClientLogAction;
use Modules\Client\Models\Enum\ClientAction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function log(string $clientUuid, ClientAction $clientAction): void
    {
        ClientLogAction::dispatch($clientUuid, request()->ip(), $clientAction);
    }


}
