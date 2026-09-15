@extends('layouts.dashboard')
@php($homeRoute = route('publisher.dashboard'))
@php($brandLabel = 'Publisher Panel')
@php($crumb = 'Account')
@php($logoutRoute = route('publisher.logout'))
@section('title', 'Notifications')
@section('nav')@include('publisher.partials.nav', ['active' => 'notifications'])@endsection
@section('content')
<div class="publisher-page-head"><div><span class="analytics-eyebrow">Updates</span><h2>Notifications</h2><p>Earnings, payouts, orders, and account activity.</p></div></div>
<section class="a-card publisher-notification-list">
    @forelse($notifications as $notification)
        <a href="{{ $notification->data['url'] ?? '#' }}" class="publisher-notification-item {{ $notification->read_at ? '' : 'unread' }}">
            <span class="publisher-notification-icon">🔔</span><div><strong>{{ $notification->data['title'] ?? 'Account update' }}</strong><p>{{ $notification->data['message'] ?? '' }}</p><small>{{ $notification->created_at->diffForHumans() }}</small></div>
        </a>
    @empty
        <div class="analytics-empty"><strong>No notifications yet</strong><span>New publisher updates will appear here.</span></div>
    @endforelse
</section>
<div style="margin-top:14px;">{{ $notifications->links() }}</div>
@endsection
