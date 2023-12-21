<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateTokenRequest;
use App\Jobs\ClientLogAction;
use App\Models\Client\Client;
use App\Models\Client\Enum\ClientAction;
use App\Models\Token;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
class TokenController extends Controller
{

    public function __construct(protected Client $client)
    {
    }
    public function generateToken(GenerateTokenRequest $request):JsonResponse
    {
        $client= $this->client->create([
            'uuid' => Str::uuid(),
            'os_type' => $request->os_type,
            'os_version' => $request->os_version,
            'model' => $request->model,
            'email' => $request->email,
            'name' => $request->name,
        ]);

        ClientLogAction::dispatch($client->uuid, $request->ip(), ClientAction::TOKEN_GENERATED);

        $token = $client->generateToken();

        Token::setCache([$token->token => $client]);

        return response()->json([
            'token' => $token->token,
            'client_id' => $client->uuid,
        ]);

    }
}
