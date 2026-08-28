@extends('layouts.app')
@section('title','Customer Support | College Street Online')
@section('content')
<section class="section account-section"><div class="container" style="max-width:1000px;">
<div class="shopping-page-head"><div><span class="eyebrow"><span class="dot"></span> Help centre</span><h1>Contact Support</h1><p>Send your question and track the response here.</p></div></div>
<div class="support-layout"><div class="card account-form-card"><div class="account-card-head"><span>?</span><div><h3>New request</h3><p>We will reply on this page.</p></div></div>
<form method="POST" action="{{ route('account.support.store') }}">@csrf
<div class="form-group"><label>Subject</label><input name="subject" value="{{ old('subject') }}" maxlength="150" required class="form-control"></div>
<div class="form-group"><label>Message</label><textarea name="message" rows="6" maxlength="5000" required class="form-control">{{ old('message') }}</textarea></div>
<button class="btn btn-primary">Submit request</button></form></div>
<div><h3>Your requests</h3>@forelse($tickets as $ticket)<article class="card support-ticket"><div class="support-ticket-head"><strong>#SUP{{ str_pad($ticket->id,4,'0',STR_PAD_LEFT) }} · {{ $ticket->subject }}</strong><span class="support-status support-{{ $ticket->status }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span></div><p>{{ $ticket->message }}</p>@if($ticket->admin_reply)<div class="support-reply"><strong>Support reply</strong><p>{{ $ticket->admin_reply }}</p><small>{{ $ticket->replied_at?->format('d M Y, h:i A') }}</small></div>@endif</article>@empty<div class="card support-empty">No support requests yet.</div>@endforelse{{ $tickets->links() }}</div></div>
</div></section>
@endsection
