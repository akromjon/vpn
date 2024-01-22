<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;

class WebsiteController extends Controller
{
    public function contact(ContactRequest $request)
    {
        $contact = $request->validated();

        Contact::create($contact);

        return response()->json([
            'message' => 'Your message has been sent successfully and we will contact you as soon as possible.',
        ]);
    }
}
