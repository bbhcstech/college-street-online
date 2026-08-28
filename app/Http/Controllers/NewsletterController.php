<?php
namespace App\Http\Controllers;

use App\Mail\NewsletterWelcomeMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);
        $email = strtolower(trim($data['email']));
        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $email],
            ['unsubscribe_token' => Str::random(48)]
        );

        if (! $subscriber->wasRecentlyCreated) {
            return back()->with('info', 'This email is already subscribed.');
        }

        try {
            Mail::to($subscriber->email)->queue(new NewsletterWelcomeMail($subscriber));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Subscribed! Check your inbox for updates.');
    }

    public function unsubscribe(string $token)
    {
        NewsletterSubscriber::where('unsubscribe_token', $token)->delete();
        return redirect()->route('home')->with('success', 'You have been unsubscribed.');
    }
}
