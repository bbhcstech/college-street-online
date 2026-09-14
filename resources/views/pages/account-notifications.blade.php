@extends('layouts.app')
@section('title', 'Notifications | College Street Online')
@section('content')
<section class="section account-section"><div class="container">
    <div class="shopping-page-head"><div><span class="eyebrow"><span class="dot"></span> My account</span><h1>Notifications</h1><p>Updates about your reviews and account activity.</p></div></div>
    <div class="customer-notification-list">
        @forelse($notifications as $notification)
            <a href="{{ $notification->data['url'] ?? '#' }}" class="card customer-notification-item {{ $notification->read_at ? '' : 'unread' }}">
                <span class="customer-notification-icon">✦</span><div><strong>{{ $notification->data['title'] ?? 'Account update' }}</strong><small>{{ $notification->created_at->diffForHumans() }}</small><p>{{ $notification->data['message'] ?? '' }}</p></div>
            </a>
        @empty
            <div class="card customer-notification-empty"><strong>No notifications yet</strong><p>New account updates will appear here.</p></div>
        @endforelse
    </div>
    {{ $notifications->links() }}
</div></section>
@endsection
