@extends('layouts.app')
@section('title', 'My Orders | College Street Online')
@section('content')
    <div class="container" style="padding-top:12px;">
        <div class="breadcrumb-row" style="margin-bottom:10px;">
            <a href="{{ route('home') }}">Home</a>
            <span class="sep">/</span>
            <span class="current">My Orders</span>
        </div>
    </div>
    <section class="account-section" style="padding: 0 0 36px 0;">
        <div class="container">
            <div class="shopping-page-head" style="margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid var(--border, #e2e8f0);">
                <div>
                    <span class="eyebrow" style="margin-bottom:4px;"><span class="dot"></span> My account</span>
                    <h1 style="font-size:1.75rem; margin:4px 0 2px 0;">Orders &amp; Tracking</h1>
                    <p style="margin:0; font-size:0.86rem; color:var(--text-secondary);">Follow fulfillment progress and review your order history.</p>
                </div>
                <a class="btn btn-outline btn-sm" href="{{ route('books.index') }}">Continue shopping</a>
            </div>
            @forelse($orders as $order)
                <div class="card customer-order-card" style="padding:16px 20px; margin-bottom:12px; border-radius:12px; border:1px solid var(--border, #e2e8f0); box-shadow: 0 4px 14px rgba(0,0,0,0.03);">
                    <div class="flex items-center" style="justify-content:space-between; flex-wrap:wrap; gap:10px;">
                        <div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span class="order-label" style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Order</span>
                                <strong style="font-size:1.05rem; color:var(--text-primary);">#CSO{{ $order->id }}</strong>
                            </div>
                            <div style="font-size:0.82rem; color:var(--text-secondary); margin-top:2px;">
                                Placed {{ $order->created_at->format('d M Y') }} &middot; {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }} &middot;
                                <strong>{{ $order->currency_symbol }}{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</strong>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            @if(in_array($order->status, ['delivered', 'completed'], true))
                                <a href="{{ route('account.orders.invoice', $order) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm" style="font-size:0.75rem; padding:4px 10px; border-radius:6px; font-weight:700;">
                                    📄 Download Invoice
                                </a>
                            @endif
                            <span class="badge {{ in_array($order->status, ['delivered', 'completed']) ? 'badge-muted' : 'badge-success' }}" style="padding:5px 12px; font-size:0.75rem; text-transform:capitalize;">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </div>
                    </div>
                    <details class="customer-tracking" style="margin-top:10px; border-top:1px solid var(--border, #f1f5f9); padding-top:10px;">
                        <summary style="cursor:pointer; font-size:0.84rem; font-weight:700; color:var(--brand-primary, #1e3a8a); display:flex; align-items:center; justify-content:space-between;">
                            <span>Track order details</span>
                            <small style="color:var(--text-secondary); font-weight:normal;">View fulfillment &amp; history &darr;</small>
                        </summary>
                        @php
                            $trackingSteps = ['pending_payment', 'confirmed', 'processing', 'packed', 'shipped', 'delivered'];
                            $currentStatus = $order->status === 'completed' ? 'delivered' : $order->status;
                            $currentStep = array_search($currentStatus, $trackingSteps, true);
                            $isStopped = in_array($order->status, ['cancelled', 'returned'], true);
                        @endphp
                        @if($isStopped)
                            <p class="tracking-notice" style="margin-top:10px; font-size:0.85rem;">This order is {{ str_replace('_', ' ', $order->status) }}.</p>
                        @else
                            <div class="status-timeline customer-status-timeline" style="margin:14px 0;">
                                @foreach($trackingSteps as $index => $step)
                                    <div class="status-step {{ $currentStep !== false && $index < $currentStep ? 'done' : '' }} {{ $currentStep === $index ? 'current' : '' }}">
                                        <div class="dot">{{ $currentStep !== false && $index < $currentStep ? '✓' : $index + 1 }}</div>
                                        <div class="lbl">{{ ucfirst(str_replace('_', ' ', $step)) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Item details with cover thumbnails --}}
                        <div class="item-fulfillment-list" style="margin-top:14px;">
                            <h4 style="font-size:0.88rem; margin:0 0 10px 0; color:var(--text-primary);">Order Items</h4>
                            @foreach($order->items as $item)
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom: 8px; padding: 8px 12px; background: var(--bg-surface-alt, #f8fafc); border-radius: 8px; border: 1px solid var(--border-color, #f1f5f9);">
                                    <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                                        @if($item->book?->cover_url)
                                            <img src="{{ $item->book->cover_url }}" alt="{{ $item->book->title }}" style="width:36px; height:46px; object-fit:contain; border-radius:4px; border:1px solid var(--border); background:#ffffff;">
                                        @else
                                            <div style="width:36px; height:46px; border-radius:4px; border:1px solid var(--border); background:#f1f5f9; display:grid; place-items:center; font-size:0.75rem; color:var(--text-muted);">📖</div>
                                        @endif
                                        <div style="min-width:0;">
                                            <strong style="display:block; font-size:0.85rem; color:var(--text-primary); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">{{ Str::title($item->book?->title ?? 'Book unavailable') }}</strong>
                                            <small style="color:var(--text-secondary); font-size:0.75rem;">by {{ $item->book?->author?->name ?? 'Unknown author' }} &bull; Qty: {{ $item->quantity }}</small>
                                        </div>
                                    </div>
                                    <div style="text-align:right; white-space:nowrap;">
                                        <strong style="display:block; font-size:0.88rem; color:var(--text-primary);">{{ $order->currency_symbol }}{{ number_format($item->quantity * $item->unit_price, 2) }}</strong>
                                        <span class="badge badge-outline" style="font-size:0.68rem; padding:2px 6px;">{{ ucfirst($item->fulfillment_status) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Direct Total Paid Summary --}}
                        <div style="margin-top: 10px; padding: 12px 16px; background: color-mix(in srgb, var(--brand-primary, #1e3a8a) 4%, var(--bg-surface-alt, #f8fafc)); border-radius: 8px; border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <span style="display:block; font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Total Amount Paid</span>
                                <strong style="font-size:1.15rem; color:var(--brand-primary, #1e3a8a);">{{ $order->currency_symbol }}{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</strong>
                            </div>
                            <div style="text-align:right;">
                                <span class="badge {{ $order->payment?->verified_status === 'verified' ? 'badge-success' : 'badge-gold' }}" style="font-size:0.76rem; padding:4px 10px;">
                                    Payment {{ ucfirst($order->payment?->verified_status ?? 'Pending') }}
                                </span>
                            </div>
                        </div>

                        {{-- Audit Log Timeline --}}
                        <div class="tracking-history" style="margin-top: 12px; font-size:0.78rem;">
                            @forelse($order->statusHistory->sortByDesc('created_at') as $history)
                                <div>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $history->to_status)) }}</strong><span>{{ $history->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                            @empty
                                <div>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</strong><span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                            @endforelse
                        </div>
                    </details>
                </div>
            @empty
                <div class="shopping-empty" style="padding:30px 15px;"><span>&#128230;</span>
                    <h2>No orders yet</h2>
                    <p>Your placed orders and tracking updates will appear here.</p><a href="{{ route('books.index') }}"
                        class="btn btn-primary">Start browsing</a>
                </div>
            @endforelse
            <div style="margin-top:16px;">{{ $orders->links() }}</div>
        </div>
    </section>
@endsection