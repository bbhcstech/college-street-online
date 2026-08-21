@extends('layouts.app')
@section('title', 'My Orders | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;"><div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">My Orders</span></div></div>
<section class="section" style="padding-top:0;">
    <div class="container">
        <h1>My Orders</h1>
        @forelse($orders as $order)
        <div class="card" style="margin-bottom:20px;">
            <div class="flex items-center" style="justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <div><strong>Order #CSO{{ $order->id }}</strong><div style="font-size:0.82rem;color:var(--text-secondary);">Placed {{ $order->created_at->format('d M Y') }} &middot; {{ $order->items->count() }} items &middot; &#8377;{{ number_format($order->total_amount, 0) }}</div></div>
                <span class="badge {{ in_array($order->status, ['delivered','completed']) ? 'badge-muted' : 'badge-success' }}">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span>
            </div>
        </div>
        @empty
            <p>You haven't placed any orders yet. <a href="{{ route('books.index') }}" style="color:var(--brand-primary);font-weight:600;">Start browsing &rarr;</a></p>
        @endforelse
        <div style="margin-top:24px;">{{ $orders->links() }}</div>
    </div>
</section>
@endsection
