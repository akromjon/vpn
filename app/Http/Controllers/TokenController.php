<?php

namespace App\Http\Controllers;

use App\Models\Token;

class TokenController extends Controller
{

    public function __construct(protected Token $token)
    {
    }
    public function generateToken():\Illuminate\Http\JsonResponse
    {
        $token = $this->token->create([
            'token' => \Illuminate\Support\Str::random(32),
            'status' => 'active'
        ]);

        return response()->json([
            'token' => $token->token
        ]);

    }
}
