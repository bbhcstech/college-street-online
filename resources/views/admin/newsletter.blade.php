@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Growth';
    $logoutRoute = route('admin.logout');
@endphp
@section('title', 'Newsletter Subscribers')
@section('nav')@include('admin.partials.nav', ['active' => 'newsletter'])@endsection
@section('content')
<div class="page-header" style="margin-bottom:22px;">
    <p style="margin:0;color:var(--a-text-muted);">Create an update and send it to your subscriber list.</p>
    <a href="{{ route('admin.newsletter.export') }}" class="btn btn-outline">↓ Export CSV</a>
</div>

<div class="a-card">
    <div class="a-card-head">
        <div class="a-card-icon">✉</div>
        <div>
            <h3 style="margin:0;">Compose newsletter</h3>
            <div style="margin-top:3px;color:var(--a-text-muted);font-size:.8rem;">This email will be sent to {{ number_format($subscribers->total()) }} {{ Str::plural('subscriber', $subscribers->total()) }}.</div>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.newsletter.send') }}" style="max-width:820px;">
        @csrf
        <div class="a-form-group">
            <label for="newsletter-subject">Subject</label>
            <input id="newsletter-subject" type="text" name="subject" value="{{ old('subject') }}" maxlength="150" required class="a-input" placeholder="Enter a clear email subject">
        </div>
        <div class="a-form-group">
            <label for="newsletter-message">Message</label>
            <textarea id="newsletter-message" name="message" maxlength="10000" required class="a-textarea" style="min-height:190px;" placeholder="Write your newsletter message here...">{{ old('message') }}</textarea>
            <div class="hint">Keep the message useful, clear, and easy to read.</div>
        </div>
        <button type="submit" class="btn btn-primary" onclick="return confirm('Send this newsletter to all subscribers?')">✉ Send to all subscribers</button>
    </form>
</div>

<div class="a-card">
    <div class="a-card-head" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div class="a-card-icon">♙</div>
            <div>
                <h3 style="margin:0;">Subscriber list</h3>
                <div style="margin-top:3px;color:var(--a-text-muted);font-size:.8rem;">{{ number_format($subscribers->total()) }} total subscribers</div>
            </div>
        </div>
        <span class="badge badge-success">Active list</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="a-table">
            <thead><tr><th>Email address</th><th>Subscribed on</th></tr></thead>
            <tbody>
            @forelse($subscribers as $s)
                <tr><td style="font-weight:600;">{{ $s->email }}</td><td style="color:var(--a-text-muted);">{{ $s->subscribed_at->format('d M Y') }}</td></tr>
            @empty
                <tr><td colspan="2" style="padding:36px;text-align:center;color:var(--a-text-muted);">No subscribers yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:18px;">{{ $subscribers->links() }}</div>
</div>
@endsection
