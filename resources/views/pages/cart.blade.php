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
        <div class="grid grid-2 cart-layout">
            <div class="card cart-items-card">
                <table class="cart-table">
                    @foreach($items as $item)
                    <tr>
                        <td><div class="cart-row-book"><div class="cart-thumb"></div><div><strong>{{ $item->book->title }}</strong><div style="font-size:0.8rem;color:var(--text-secondary);">{{ $item->book->author->name ?? '' }}</div></div></div></td>
                        <td>
                            <form method="POST" action="{{ route('cart.update', $item) }}" class="qty-stepper" data-cart-quantity-form>
                                @csrf @method('PATCH')
                                <button type="button" aria-label="Decrease quantity" onclick="this.form.quantity.stepDown(); this.form.requestSubmit()" {{ $item->quantity <= 1 ? 'disabled' : '' }}>&minus;</button>
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" aria-label="Quantity" onchange="this.form.requestSubmit()">
                                <button type="button" aria-label="Increase quantity" onclick="this.form.quantity.stepUp(); this.form.requestSubmit()">+</button>
                            </form>
                        </td>
                        <td style="font-family:var(--font-heading);font-weight:700;" data-line-total>&#8377;{{ number_format($item->lineTotal(), 0) }}</td>
                        <td><form method="POST" action="{{ route('cart.destroy', $item) }}">@csrf @method('DELETE')<button type="submit" class="btn btn-outline btn-sm">Remove</button></form></td>
                    </tr>
                    @endforeach
                </table>
            </div>
            <div class="order-summary-card">
                <h3 style="margin-top:0;">Order Summary</h3>
                <div class="summary-line"><span>Subtotal</span><span data-cart-subtotal>&#8377;{{ number_format($quote['subtotal'], 0) }}</span></div>
                <div class="summary-line"><span>Shipping</span><span data-cart-shipping>{{ $quote['shipping'] == 0 ? 'Free' : '₹'.number_format($quote['shipping'],0) }}</span></div>
                <div class="summary-line"><span>Platform Fee</span><span data-cart-platform-fee>&#8377;{{ number_format($quote['platformFee'], 0) }}</span></div>
                <div class="summary-line total"><span>Total</span><span data-cart-total>&#8377;{{ number_format($quote['total'], 0) }}</span></div>
                <a href="{{ route('checkout.index') }}" class="btn btn-primary" style="width:100%;margin-top:16px;">Proceed to Checkout</a>
            </div>
        </div>
        @endif
    </div>
</section>
<script>
document.querySelectorAll('[data-cart-quantity-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const input = form.querySelector('[name="quantity"]');
        const buttons = form.querySelectorAll('button');
        buttons.forEach((button) => button.disabled = true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: new FormData(form),
            });
            if (!response.ok) throw new Error('Quantity update failed.');

            const data = await response.json();
            const money = (value) => `₹${Number(value).toLocaleString('en-IN', { maximumFractionDigits: 0 })}`;
            form.closest('tr').querySelector('[data-line-total]').textContent = money(data.line_total);
            document.querySelector('[data-cart-subtotal]').textContent = money(data.quote.subtotal);
            document.querySelector('[data-cart-shipping]').textContent = Number(data.quote.shipping) === 0 ? 'Free' : money(data.quote.shipping);
            document.querySelector('[data-cart-platform-fee]').textContent = money(data.quote.platformFee);
            document.querySelector('[data-cart-total]').textContent = money(data.quote.total);
        } catch (error) {
            window.location.reload();
        } finally {
            buttons.forEach((button) => button.disabled = false);
            form.querySelector('[aria-label="Decrease quantity"]').disabled = Number(input.value) <= 1;
        }
    });
});
</script>
@endsection
