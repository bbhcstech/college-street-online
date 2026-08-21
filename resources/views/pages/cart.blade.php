@extends('layouts.app')
@section('title', 'Your Cart | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;"><div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">Cart</span></div></div>
<section class="section" style="padding-top:0;">
    <div class="container">
        <h1>Your Cart <span style="color:var(--text-secondary);font-size:1rem;font-weight:400;">({{ $items->count() }} items)</span></h1>
        @if($items->isEmpty())
            <p>Your cart is empty. <a href="{{ route('books.index') }}" style="color:var(--brand-primary);font-weight:600;">Browse books &rarr;</a></p>
        @else
        <div class="grid grid-2" style="grid-template-columns:1.6fr 1fr;align-items:start;gap:32px;">
            <div class="card" style="padding:8px 24px;">
                <table class="cart-table">
                    @foreach($items as $item)
                    <tr>
                        <td><div class="cart-row-book"><div class="cart-thumb"></div><div><strong>{{ $item->book->title }}</strong><div style="font-size:0.8rem;color:var(--text-secondary);">{{ $item->book->author->name ?? '' }}</div></div></div></td>
                        <td>
                            <form method="POST" action="{{ route('cart.update', $item) }}" class="qty-stepper">
                                @csrf @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" style="width:50px;border:none;background:none;text-align:center;">
                                <button type="submit">Update</button>
                            </form>
                        </td>
                        <td style="font-family:var(--font-heading);font-weight:700;">&#8377;{{ number_format($item->lineTotal(), 0) }}</td>
                        <td><form method="POST" action="{{ route('cart.destroy', $item) }}">@csrf @method('DELETE')<button type="submit" class="btn btn-outline btn-sm">Remove</button></form></td>
                    </tr>
                    @endforeach
                </table>
            </div>
            <div class="order-summary-card">
                <h3 style="margin-top:0;">Order Summary</h3>
                <div class="summary-line"><span>Subtotal</span><span>&#8377;{{ number_format($quote['subtotal'], 0) }}</span></div>
                <div class="summary-line"><span>Shipping</span><span>{{ $quote['shipping'] == 0 ? 'Free' : '₹'.number_format($quote['shipping'],0) }}</span></div>
                <div class="summary-line"><span>Platform Fee</span><span>&#8377;{{ number_format($quote['platformFee'], 0) }}</span></div>
                <div class="summary-line total"><span>Total</span><span>&#8377;{{ number_format($quote['total'], 0) }}</span></div>
                <a href="{{ route('checkout.index') }}" class="btn btn-primary" style="width:100%;margin-top:16px;">Proceed to Checkout</a>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
