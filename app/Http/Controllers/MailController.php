<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\MailService;

class MailController extends Controller
{
    private MailService $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function show()
    {
        return view('mails.show_form');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $this->mailService->sendContactMessage($validated);

        return redirect()->back()->with(
            'success',
            'На почту ' . $validated['email'] . ' отправлено письмо'
        );
    }


}
