<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\ContactFormMail;

class ContactController extends Controller
{
    /**
     * Display the contact form.
     */
    public function create()
    {
        return view('contact.form');
    }

    /**
     * Send the contact form submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'newsletter' => ['nullable', 'boolean'],
        ]);

        // Send email
        Mail::to('glorygandigbe2@gmail.com')->send(new ContactFormMail(
            $validated['name'],
            $validated['email'],
            $validated['subject'],
            $validated['message'],
            $validated['newsletter'] ?? false
        ));

        // If user is authenticated and wants newsletter, subscribe
        if (Auth::check() && ($validated['newsletter'] ?? false)) {
            Auth::user()->customerProfile->update([
                'newsletter_status' => 'subscribed',
            ]);
        }

        return back()->with('success', 'Message envoyé avec succès ! Nous vous répondrons bientôt.');
    }
}
