<?php

namespace Modules\Server\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PritunlActionRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pritunl_user_id' => 'required|string|max:255',
            'state' => 'required|string|max:255|in:connected,disconnected',
            'client_uuid'=>'required|string|max:255'
        ];
    }
}
