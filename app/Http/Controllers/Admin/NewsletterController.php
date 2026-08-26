<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterUpdateMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 10;
        $subscribers = NewsletterSubscriber::query()
            ->when($request->filled('q'), fn ($query) => $query->where('email', 'like', '%'.trim($request->query('q')).'%'))
            ->latest('subscribed_at')->paginate($perPage)->withQueryString();

        return view('admin.newsletter', [
            'subscribers' => $subscribers,
            'totalSubscribers' => NewsletterSubscriber::count(),
            'newThisMonth' => NewsletterSubscriber::where('subscribed_at', '>=', now()->startOfMonth())->count(),
            'latestSubscription' => NewsletterSubscriber::max('subscribed_at'),
        ]);
    }

    public function export(): StreamedResponse
    {
        $subscribers = NewsletterSubscriber::orderBy('subscribed_at')->get();
        return response()->streamDownload(function () use ($subscribers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Email', 'Subscribed At']);
            foreach ($subscribers as $s) {
                fputcsv($out, [$s->email, $s->subscribed_at]);
            }
            fclose($out);
        }, 'newsletter-subscribers.csv');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:10000',
        ]);

        $count = NewsletterSubscriber::count();
        NewsletterSubscriber::query()->chunkById(100, function ($subscribers) use ($data) {
            foreach ($subscribers as $subscriber) {
                Mail::to($subscriber->email)->queue(
                    new NewsletterUpdateMail($subscriber, $data['subject'], $data['message'])
                );
            }
        });

        return back()->with('success', "Newsletter queued for {$count} subscribers.");
    }
}
