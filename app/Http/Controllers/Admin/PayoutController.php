<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublisherLedgerEntry;
use App\Models\PublisherPayoutRequest;
use App\Notifications\PublisherFinanceNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = PublisherPayoutRequest::with(['publisher.user', 'reviewer'])->latest()->paginate(20);
        return view('admin.payouts.index', compact('payouts'));
    }

    public function update(Request $request, PublisherPayoutRequest $payout)
    {
        $data = $request->validate(['status' => ['required', 'in:approved,paid,rejected'], 'reference' => ['nullable', 'required_if:status,paid', 'string', 'max:100'], 'admin_note' => ['nullable', 'string', 'max:1000']]);
        abort_if(in_array($payout->status, ['paid', 'rejected'], true), 422, 'This payout is already closed.');

        DB::transaction(function () use ($payout, $data) {
            if ($data['status'] === 'paid') {
                $available = (float) PublisherLedgerEntry::where('publisher_id', $payout->publisher_id)->where('available_at', '<=', now())->lockForUpdate()->sum('amount');
                abort_if((float) $payout->amount > $available, 422, 'Publisher balance is no longer sufficient.');
                PublisherLedgerEntry::firstOrCreate(['reference_key' => 'payout:'.$payout->id], ['publisher_id' => $payout->publisher_id, 'payout_request_id' => $payout->id, 'type' => 'payout', 'amount' => -abs((float) $payout->amount), 'description' => 'Manual payout '.$data['reference'], 'available_at' => now()]);
            }
            $payout->update(['status' => $data['status'], 'reference' => $data['reference'] ?? null, 'admin_note' => $data['admin_note'] ?? null, 'reviewed_by' => auth()->id(), 'reviewed_at' => now(), 'paid_at' => $data['status'] === 'paid' ? now() : null]);
        });

        $payout->publisher->user?->notify(new PublisherFinanceNotification('Payout '.ucfirst($data['status']), 'Your payout request of ₹'.number_format($payout->amount, 2).' was '.$data['status'].'.', route('publisher.payments.index')));
        return back()->with('success', 'Payout status updated.');
    }
}
