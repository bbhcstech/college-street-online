@extends('layouts.app')
@section('title', 'Your Cart | College Street Online')
@section('content')
    <div class="container" style="padding-top:24px;">
        <div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span
                class="current">Cart</span></div>
    </div>
    <section class="section shopping-section">
        <div class="container">
            <div class="shopping-page-head">
                <div><span class="eyebrow"><span class="dot"></span> Shopping bag</span>
                    <h1>Your Cart</h1>
                    <p>{{ $items->count() }} {{ Str::plural('book', $items->count()) }} selected</p>
                </div><a href="{{ route('books.index') }}" class="btn btn-outline">Continue shopping</a>
            </div>
            @if($items->isEmpty())
                <div class="shopping-empty"><span>&#128722;</span>
                    <h2>Your cart is empty</h2>
                    <p>Explore the catalogue and add books you would like to order.</p><a href="{{ route('books.index') }}"
                        class="btn btn-primary">Browse books</a>
                </div>
            @else
                <div class="grid grid-2 cart-layout">
                    <div class="card cart-items-card">
                        <table class="cart-table">
                            @foreach($items as $item)
                                <tr>
                                    <td>
                                        <div class="cart-row-book">
                                            @if($item->book->cover_url)
                                                <img src="{{ $item->book->cover_url }}" alt="{{ $item->book->title }} cover"
                                                    class="cart-thumb" style="object-fit:cover;">
                                            @else
                                                <div class="cart-thumb" aria-hidden="true"></div>
                                            @endif
                                            <div><a
                                                    href="{{ route('books.show', $item->book) }}"><strong>{{ $item->book->title }}</strong></a>
                                                <div class="cart-book-meta">by {{ $item->book->author->name ?? 'Unknown author' }}
                                                </div><span class="stock-pill in">&#9679; In stock</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('cart.update', $item) }}" class="qty-stepper"
                                            data-cart-quantity-form>
                                            @csrf @method('PATCH')
                                            <button type="button" aria-label="Decrease quantity"
                                                onclick="this.form.quantity.stepDown(); this.form.requestSubmit()" {{ $item->quantity <= 1 ? 'disabled' : '' }}>&minus;</button>
                                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                                aria-label="Quantity" onchange="this.form.requestSubmit()">
                                            <button type="button" aria-label="Increase quantity"
                                                onclick="this.form.quantity.stepUp(); this.form.requestSubmit()">+</button>
                                        </form>
                                    </td>
                                    <td style="font-family:var(--font-heading);font-weight:700;" data-line-total>
                                        &#8377;{{ number_format($item->lineTotal(), 0) }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('cart.destroy', $item) }}">@csrf
                                            @method('DELETE')<button type="submit" class="btn btn-outline btn-sm">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <div class="order-summary-card">
                        <div class="summary-heading"><span>Order summary</span><small>{{ $items->count() }}
                                {{ Str::plural('title', $items->count()) }}</small></div>
                        <div class="summary-line"><span>Subtotal</span><span
                                data-cart-subtotal>&#8377;{{ number_format($quote['subtotal'], 0) }}</span></div>
                        <div class="summary-line"><span>Shipping</span><span
                                data-cart-shipping>{{ $quote['shipping'] == 0 ? 'Free' : '₹' . number_format($quote['shipping'], 0) }}</span>
                        </div>
                        <div class="summary-line"><span>Platform Fee</span><span
                                data-cart-platform-fee>&#8377;{{ number_format($quote['platformFee'], 0) }}</span></div>
                        <div class="summary-line total"><span>Total</span><span
                                data-cart-total>&#8377;{{ number_format($quote['total'], 0) }}</span></div>
                        <a href="{{ route('checkout.index') }}" class="btn btn-primary"
                            style="width:100%;margin-top:16px;">Proceed to Checkout</a>
                        <div class="summary-assurance">&#128274; Secure checkout &middot; Login protected</div>
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