<?php

namespace App\Http\Controllers;

use App\Models\Client\Client;
use App\Models\Client\Enum\ClientAction;
use App\Models\Token;
use Illuminate\Http\Request;



class ClientController extends Controller
{

    public function getMe()
    {
        $client = Client::select('last_used_at', 'status', 'uuid')->whereHas('token', function ($query) {
            return $query->where('token', request()->header('TOKEN'));
        })->first();

        return response()->json([
            'token' => request()->header('TOKEN'),
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
