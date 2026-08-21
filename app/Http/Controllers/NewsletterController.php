<?php
namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);
        NewsletterSubscriber::firstOrCreate(
            ['email' => $data['email']],
            ['unsubscribe_token' => Str::random(48)]
        );
        return back()->with('success', 'Subscribed! Check your inbox for updates.');
    }

    public function unsubscribe(string $token)
    {
        NewsletterSubscriber::where('unsubscribe_token', $token)->delete();
        return redirect()->route('home')->with('success', 'You have been unsubscribed.');
    }
}
