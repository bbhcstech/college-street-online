@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Marketplace / Reviews';
    $logoutRoute = route('admin.logout');
@endphp
@section('title', 'Review Details')
@section('nav')@include('admin.partials.nav', ['active' => 'reviews'])@endsection
@section('content')
<div class="review-detail-head">
    <div><span class="analytics-eyebrow">Review moderation</span><h2>Review Details</h2><p>Verified purchase review #{{ $review->id }}</p></div>
    <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline">← Back to reviews</a>
</div>

<div class="review-detail-grid">
    <section class="a-card review-detail-main">
        <div class="review-detail-book">
            @if($review->book?->cover_url)<img src="{{ $review->book->cover_url }}" alt="{{ $review->book->title }} cover">@endif
            <div><small>Book purchased</small><h3>{{ $review->book?->title ?? 'Deleted book' }}</h3><span>Order #CSO{{ $review->order_id }} · Verified purchase</span></div>
        </div>
        <div class="review-detail-rating"><span>{!! str_repeat('★', $review->rating) !!}{!! str_repeat('☆', 5 - $review->rating) !!}</span><strong>{{ $review->rating }} / 5</strong></div>
        <div class="review-detail-text"><small>Full review</small><p>{{ $review->review ?: 'No written review was provided.' }}</p></div>
        @if($review->images)<div class="review-detail-images"><small>Customer images</small><div>@foreach($review->images as $image)<a href="{{ asset('storage/'.$image) }}" target="_blank"><img src="{{ asset('storage/'.$image) }}" alt="Customer review image"></a>@endforeach</div></div>@endif
    </section>

    <aside class="review-detail-side">
        <section class="a-card review-detail-meta"><h3>Customer &amp; order</h3><dl><dt>Customer</dt><dd>{{ $review->customer?->name ?? 'Deleted customer' }}</dd><dt>Email</dt><dd>{{ $review->customer?->email ?? '—' }}</dd><dt>Order ID</dt><dd>#CSO{{ $review->order_id }}</dd><dt>Review date</dt><dd>{{ $review->created_at->format('d M Y, h:i A') }}</dd><dt>Purchase</dt><dd><span class="badge badge-success">Verified</span></dd></dl></section>
        <section class="a-card review-detail-reports"><h3>Report information</h3>@forelse($review->reports as $report)<div><strong>{{ $report->reporter?->name ?? 'Deleted customer' }}</strong><small>{{ $report->created_at->format('d M Y, h:i A') }}</small><p>{{ $report->reason }}</p></div>@empty<p class="review-detail-empty">No reports for this review.</p>@endforelse</section>
    </aside>

    <section class="a-card review-response-card"><h3>Admin response</h3><p>Shown publicly beneath the customer review.</p><form method="POST" action="{{ route('admin.reviews.response', $review) }}">@csrf @method('PATCH')<textarea name="admin_response" class="a-textarea" maxlength="2000" placeholder="Write a helpful public response...">{{ old('admin_response', $review->admin_response) }}</textarea><div><small>{{ $review->admin_responded_at?->format('d M Y, h:i A') }}</small><button class="btn btn-primary">Save response</button></div></form></section>
</div>
@endsection
