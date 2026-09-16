<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\PublisherLedgerEntry;
use App\Models\PublisherActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:1'], 'payment_details' => ['required', 'string', 'max:1000']]);
        $publisher = $request->user()->publisher;

        DB::transaction(function () use ($publisher, $data) {
            $available = (float) PublisherLedgerEntry::where('publisher_id', $publisher->id)->where('available_at', '<=', now())->lockForUpdate()->sum('amount');
            $reserved = (float) $publisher->payoutRequests()->whereIn('status', ['requested', 'approved'])->sum('amount');
            abort_if((float) $data['amount'] > round($available - $reserved, 2), 422, 'Requested amount exceeds the available balance.');
            $publisher->payoutRequests()->create($data);
        });

        PublisherActivity::record($publisher, 'payout_requested', 'Payout request', 'Payout request for ₹'.number_format((float) $data['amount'], 2).' submitted.');

        return back()->with('success', 'Payout request submitted for admin approval.');
    }
}
