<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends Controller
{
    public function index()
    {
        return view('admin.newsletter', ['subscribers' => NewsletterSubscriber::latest('subscribed_at')->paginate(30)]);
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
}
