<?php

namespace Modules\Client\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Client\Models\Client;
use Modules\Client\Models\Token;
use Modules\Client\Models\Enum\ClientAction;

class ClientController extends Controller
{

    public function getMe()
    {
        $client = Client::select('last_used_at', 'status','monetization_type as ad_type','uuid')->whereHas('token', function ($query) {
            return $query->where('token', request()->header('TOKEN'));
        })->first();

        $client->token=request()->header('TOKEN');

        return response()->json([
            'me' => $client,
        ]);
    }
    public function status()
    {
        $client = Token::getClient();

        $lastConnection = $client->connections->last();

        if (!$lastConnection || $lastConnection->status == 'disconnected') {

            return response()->json([
                "status" => 'disconnected',
                'message' => 'Disconnected from server!',
            ]);
        }

        if ($lastConnection->status == 'connected') {

            return response()->json([
                "status" => 'connected',
                'message' => "Connected to {$lastConnection->pritunlUser->server_ip}",
            ]);
        }

        return response()->json([
            "status" => 'ready_to_connect',
            'message' => 'Ready to connect!',
        ]);

    }


    public function delete()
    {
        $client = Token::getClient();

        $client->update(['status' => 'deleted']);

        $this->act(Token::getCachedClientUuid(), ClientAction::DELETED_APP);

        return response()->json(["message" => "Deleted"]);
    }

}
