<?php

namespace Modules\Client\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Client\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Client\Http\Requests\GenerateTokenRequest;
use Modules\Client\Models\Enum\ClientAction;
use Modules\Client\Models\Token;

class TokenController extends Controller
{

    public function __construct(protected Client $client)
    {
    }
    public function generateToken(GenerateTokenRequest $request): JsonResponse
    {
        $client = $this->client->create([
            'uuid' => Str::uuid(),
            'os_type' => $request->os_type,
            'os_version' => $request->os_version,
            'model' => $request->model,
            'email' => $request->email,
            'name' => $request->name,
            'monetization_type'=>['free','rewarded','app_open','interstitial']
        ]);

        $this->log($client->uuid,ClientAction::TOKEN_GENERATED);

        $token = $client->generateToken();

        Token::setCache([$token->token => $client->uuid]);

        return response()->json([
            'token' => $token->token,
        ]);

    }
}
