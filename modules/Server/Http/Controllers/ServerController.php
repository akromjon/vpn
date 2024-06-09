<?php

namespace Modules\Server\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\Client\Models\Client;
use Modules\Client\Models\Enum\ClientAction;
use Modules\Client\Models\Token;
use Modules\Pritunl\Models\PritunlUser;
use Modules\Server\Http\Requests\PritunlActionRequest;
use Modules\Pritunl\Repository\PritunlUserRepository;
use Modules\Server\Jobs\UserAction;
use Modules\Server\Repository\ServerRepository;

class ServerController extends Controller
{

    public function __construct(
        protected ServerRepository $ServerRepository,
        protected PritunlUserRepository $pritunlUserRepository)
    {
    }
    public function list()
    {
        $this->log(Token::getCachedClientUuid(), ClientAction::LIST_SERVERS);

        $results = $this->ServerRepository->list();

        if ($results->isEmpty()) {

            return response()->json([
                "message" => "Servers not found",
                'code' => 3000
            ], 404);
        }

        return response()->json($results);
    }

    public function download(string $ip)
    {
        $client = Token::getClient();

        $server = $this->ServerRepository->downloadConfig($client, $ip);

        if (empty($server)) {

            cache()->forget("{$client->uuid}:{$ip}:pritunl_users");

            return response()->json([
                "message" => "Server not found",
                'code' => 3000
            ], 404);
        }

        $this->log(Token::getCachedClientUuid(), ClientAction::DOWNLOADED_CONFIG);

        return response()->download($server->vpn_config_path, 'vpn_config.ovpn');
    }

    public function pritunlUserAction(PritunlActionRequest $request)
    {
        $request = $request->validated();

        UserAction::dispatch($request);

        return response()->json([
            "message"=>"the job has been queued and processed soon!"
        ]);
    }
}
