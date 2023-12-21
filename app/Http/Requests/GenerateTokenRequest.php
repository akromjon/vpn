<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateTokenRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'os_type' => 'required|string|max:20|in:android,ios,mac,linux,windows',
            'os_version' => 'required|string|max:20',
            'model' => 'required|string|max:50|min:3',
            'email' => 'nullable|email|unique:clients,email',
            "name"=> "nullable|string|max:255|min:3"
        ];
    }
}
