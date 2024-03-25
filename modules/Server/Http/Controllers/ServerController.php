<?php

namespace Modules\Server\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Server\Jobs\Download;
use Modules\Server\Jobs\Wait;
use Modules\Server\Models\Enum\ServerStatus;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Client;
use Modules\Client\Models\ClientPritunlUserConnection;
use Modules\Client\Models\Enum\ClientAction;
use Modules\Client\Models\Token;
use Modules\Pritunl\Models\Enum\InternalServerStatus;
use Modules\Pritunl\Models\Enum\PritunlStatus;
use Modules\Pritunl\Models\Enum\PritunlSyncStatus;
use Modules\Pritunl\Models\PritunlUser;
use Modules\Pritunl\Models\Enum\PritunlUserStatus;
use Modules\Server\Http\Requests\PritunlActionRequest;
use Illuminate\Support\Facades\File;
use Modules\Pritunl\Repository\PritunlUserRepository;
use Modules\Server\Repository\ServerRepository;

class ServerController extends Controller
{

    public function __construct(protected ServerRepository $ServerRepository, protected PritunlUserRepository $pritunlUserRepository)
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

        $server = cache()->remember("{$client->uuid}:{$ip}:pritunl_users", 30, function () use ($client, $ip) {

            $server = $this->ServerRepository->get($ip);

            if (!$server) {

                return null;
            }

            $this->act(Token::getCachedClientUuid(), ClientAction::DOWNLOADED_CONFIG);

            $pritunlUser=$this->pritunlUserRepository->updateToInUse($server->pritunl_user_id);

            $this->cleanFile($server->vpn_config_path);

            File::append($server->vpn_config_path, PHP_EOL . "setenv UV_CLIENT_UUID {$client->uuid}" . PHP_EOL);

            Wait::dispatch($pritunlUser, $client)->delay(now()->addSeconds(30));

            return $server;
        });

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

        $state = $request['state'];

        $pritunlUser = PritunlUser::where("internal_user_id", $request['pritunl_user_id'])->first();

        if (!$pritunlUser) {

            return response()->json(["message" => "Pritunl user not found"], 404);
        }

        $client = Client::where("uuid", $request['client_uuid'])->first();

        if (!$client) {

            return response()->json(["message" => "Client not found"], 404);
        }

        return $this->$state($client, $pritunlUser->id);
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

    private function connected(Client $client, int $pritunlId)
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

        return response()->json(["message" => "Connected"]);
    }

    private function disconnected(Client $client, int $pritunlId = 0)
    {
        $lastConnection = $client->connections->last();

        if (!$lastConnection || $lastConnection->status == 'idle') {

            return response()->json([
                "message" => "Need to be connected to disconnect",
                'code' => 3020
            ], 400);
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

        return response()->json(["message" => "Disconnected"]);
    }
}
