<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WebsiteController extends Controller
{
    public function home()
    {
        return view('web.pages.home');
    }

    public function contactUs()
    {
        return view('web.pages.contact-us');
    }

    public function submitContact(ContactRequest $request)
    {
        $contactMessage = ContactMessage::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $recipientEmail = 'e.mon143298@gmail.com';

        if (!empty($recipientEmail)) {
            try {
                Mail::to($recipientEmail)->send(new ContactMessageMail($contactMessage));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', 'Thanks for contacting us! We have received your message.');
    }

    public function problems()
    {
        return view('web.pages.coming-soon', ['title' => 'Problems']);
    }

    public function contests()
    {
        return view('web.pages.coming-soon', ['title' => 'Contests']);
    }

    public function community()
    {
        return view('web.pages.coming-soon', ['title' => 'Community']);
    }
}
