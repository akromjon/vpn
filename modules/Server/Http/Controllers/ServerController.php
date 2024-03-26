<?php

namespace Modules\Server\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\Client\Models\Client;
use Modules\Client\Models\Enum\ClientAction;
use Modules\Client\Models\Token;
use Modules\Pritunl\Models\PritunlUser;
use Modules\Server\Http\Requests\PritunlActionRequest;
use Modules\Pritunl\Repository\PritunlUserRepository;
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
        $this->act(Token::getCachedClientUuid(), ClientAction::LIST_SERVERS);

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

        $this->act(Token::getCachedClientUuid(), ClientAction::DOWNLOADED_CONFIG);

        if (empty($server)) {

            cache()->forget("{$client->uuid}:{$ip}:pritunl_users");

            return response()->json([
                "message" => "Server not found",
                'code' => 3000
            ], 404);
        }

        return response()->download($server->vpn_config_path, 'vpn_config.ovpn');
    }

    public function pritunlUserAction(PritunlActionRequest $request)
    {
        $request = $request->validated();

        $pritunlUser = PritunlUser::where("internal_user_id", $request['pritunl_user_id'])->first();

        if (!$pritunlUser) {

            return response()->json(["message" => "Pritunl user not found"], 404);
        }

        $client = Client::where("uuid", $request['client_uuid'])->first();

        if (!$client) {

            return response()->json(["message" => "Client not found"], 404);
        }

        // $request['state'] in [connected,disconnected]

        $state=$request['state'];

        $action = $this->ServerRepository->$state($client, $pritunlUser->id);

        if (!$action) {

            return response()->json([
                "message" => "Need to be connected to disconnect",
                'code' => 3020
            ], 400);

        }

        return response()->json(["message" => $state]);
    }
}
