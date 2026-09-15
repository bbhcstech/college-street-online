@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Payments')
@php($logoutRoute = route('admin.logout'))
@section('title', 'Publisher Payouts')
@section('nav')@include('admin.partials.nav', ['active' => 'payouts'])@endsection
@section('content')
<div class="publisher-page-head"><div><span class="analytics-eyebrow">Settlements</span><h2>Publisher payout requests</h2><p>Review and record manual transfers. Mark paid only after completing the bank or UPI transfer.</p></div></div>
<section class="a-card publisher-table-card"><div class="publisher-table-scroll"><table class="a-table"><thead><tr><th>Requested</th><th>Publisher</th><th>Amount</th><th>Payment details</th><th>Status</th><th>Reference</th><th>Action</th></tr></thead><tbody>
@forelse($payouts as $payout)<tr><td>{{ $payout->created_at->format('d M Y') }}</td><td><strong>{{ $payout->publisher->business_name }}</strong><small>{{ $payout->publisher->user?->email }}</small></td><td><strong>₹{{ number_format($payout->amount, 2) }}</strong></td><td style="white-space:pre-line;max-width:260px;">{{ $payout->payment_details }}</td><td><span class="badge badge-info">{{ ucfirst($payout->status) }}</span></td><td>{{ $payout->reference ?? '—' }}</td><td>
@if(!in_array($payout->status, ['paid','rejected']))<form method="POST" action="{{ route('admin.payouts.update', $payout) }}">@csrf @method('PATCH')<select name="status" class="a-select" required><option value="approved">Approve</option><option value="paid">Mark paid</option><option value="rejected">Reject</option></select><input name="reference" class="a-input" placeholder="Transfer reference"><input name="admin_note" class="a-input" placeholder="Admin note"><button class="btn btn-primary btn-sm">Update</button></form>@else<span class="a-muted">Closed</span>@endif
</td></tr>@empty<tr><td colspan="7">No payout requests.</td></tr>@endforelse
</tbody></table></div><div class="publisher-table-footer">{{ $payouts->links() }}</div></section>
@endsection
