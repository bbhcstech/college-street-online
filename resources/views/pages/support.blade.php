@extends('layouts.app')
@section('title', 'Customer Support | College Street Online')
@section('content')
    <div class="container" style="padding-top:12px;">
        <div class="breadcrumb-row" style="margin-bottom:10px;">
            <a href="{{ route('home') }}">Home</a>
            <span class="sep">/</span>
            <span class="current">Customer Support</span>
        </div>
    </div>

    <section class="account-section" style="padding: 0 0 40px 0;">
        <div class="container">
            {{-- Page Header --}}
            <div class="shopping-page-head" style="margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid var(--border, #e2e8f0);">
                <div>
                    <span class="eyebrow" style="margin-bottom:4px;"><span class="dot"></span> Help &amp; Support</span>
                    <h1 style="font-size:1.75rem; margin:4px 0 2px 0; color:var(--text-primary);">Contact Customer Support</h1>
                    <p style="margin:0; font-size:0.86rem; color:var(--text-secondary);">Have a question or issue with an order? Send a request and track status updates here.</p>
                </div>
            </div>

            {{-- Support Grid --}}
            <div class="support-grid">
                {{-- Left: New Ticket Form --}}
                <div class="support-card-form" style="background:var(--surface, #ffffff); border:1px solid var(--border, #e2e8f0); border-radius:14px; padding:22px; box-shadow:0 4px 16px rgba(0,0,0,0.03); height:fit-content;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border, #f1f5f9);">
                        <div style="width:40px; height:40px; border-radius:10px; background:color-mix(in srgb, var(--brand-primary, #1e3a8a) 10%, var(--surface, #ffffff)); display:grid; place-items:center; font-size:1.2rem; color:var(--brand-primary, #1e3a8a);">
                            💬
                        </div>
                        <div>
                            <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:var(--text-primary);">New Support Request</h3>
                            <p style="margin:2px 0 0 0; font-size:0.78rem; color:var(--text-secondary);">Our support team typically replies within 24 hours.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('account.support.store') }}">
                        @csrf
                        <div style="margin-bottom:14px;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">
                                Subject <span style="color:#ef4444;">*</span>
                            </label>
                            <input name="subject" value="{{ old('subject') }}" maxlength="150" required 
                                   placeholder="e.g., Question about Order #CSO5 or Delivery" 
                                   class="form-control"
                                   style="width:100%; padding:10px 14px; font-size:0.88rem; border:1px solid var(--border, #cbd5e1); border-radius:8px; outline:none; transition:all 0.2s ease;">
                        </div>

                        <div style="margin-bottom:18px;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">
                                Message Details <span style="color:#ef4444;">*</span>
                            </label>
                            <textarea name="message" rows="5" maxlength="5000" required 
                                      placeholder="Describe your issue or request in detail..." 
                                      class="form-control"
                                      style="width:100%; padding:10px 14px; font-size:0.88rem; border:1px solid var(--border, #cbd5e1); border-radius:8px; outline:none; resize:vertical; transition:all 0.2s ease;">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%; padding:11px; font-size:0.9rem; font-weight:700; border-radius:8px; display:flex; align-items:center; justify-content:center; gap:8px;">
                            <span>📨</span> Submit Request
                        </button>
                    </form>
                </div>

                {{-- Right: Ticket History --}}
                <div class="support-card-history">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                        <h3 style="margin:0; font-size:1.1rem; font-weight:700; color:var(--text-primary);">Your Requests</h3>
                        <span class="badge badge-muted" style="font-size:0.75rem; padding:4px 10px; border-radius:12px;">
                            {{ $tickets->total() }} {{ Str::plural('Ticket', $tickets->total()) }}
                        </span>
                    </div>

                    @forelse($tickets as $ticket)
                        <article class="support-ticket-item" style="background:var(--surface, #ffffff); border:1px solid var(--border, #e2e8f0); border-radius:12px; padding:18px; margin-bottom:14px; box-shadow:0 2px 8px rgba(0,0,0,0.02); transition:transform 0.15s ease;">
                            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:8px;">
                                <div>
                                    <span style="font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">
                                        #SUP-{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }} &bull; {{ $ticket->created_at->format('d M Y, h:i A') }}
                                    </span>
                                    <h4 style="margin:4px 0 0 0; font-size:0.98rem; font-weight:700; color:var(--text-primary); line-height:1.3;">
                                        {{ $ticket->subject }}
                                    </h4>
                                </div>
                                <span class="badge {{ $ticket->status === 'resolved' || $ticket->status === 'closed' ? 'badge-success' : 'badge-gold' }}" 
                                      style="font-size:0.75rem; padding:4px 10px; text-transform:capitalize; white-space:nowrap; border-radius:6px;">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </div>

                            <p class="ticket-msg-box" style="margin:8px 0 0 0; font-size:0.86rem; color:var(--text-secondary); line-height:1.5; background:var(--surface-alt, #f8fafc); padding:10px 14px; border-radius:8px; border:1px solid var(--border, #f1f5f9);">
                                {{ $ticket->message }}
                            </p>

                            @if($ticket->admin_reply)
                                <div class="ticket-reply-box" style="margin-top:12px; padding:12px 14px; background:color-mix(in srgb, var(--brand-primary, #1e3a8a) 4%, var(--surface, #ffffff)); border:1px solid color-mix(in srgb, var(--brand-primary, #1e3a8a) 18%, var(--border, #e2e8f0)); border-radius:10px;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                                        <div style="display:flex; align-items:center; gap:6px; font-size:0.82rem; font-weight:700; color:var(--brand-primary, #1e3a8a);">
                                            <span>🎧</span> Support Response
                                        </div>
                                        @if($ticket->replied_at)
                                            <small style="font-size:0.72rem; color:var(--text-muted);">{{ $ticket->replied_at->format('d M Y, h:i A') }}</small>
                                        @endif
                                    </div>
                                    <p style="margin:0; font-size:0.86rem; color:var(--text-primary); line-height:1.5;">
                                        {{ $ticket->admin_reply }}
                                    </p>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="support-empty-box" style="background:var(--surface, #ffffff); border:1px dashed var(--border, #cbd5e1); border-radius:14px; padding:36px 20px; text-align:center;">
                            <div style="width:54px; height:54px; margin:0 auto 12px auto; border-radius:50%; background:var(--surface-alt, #f1f5f9); display:grid; place-items:center; font-size:1.6rem; color:var(--text-muted);">
                                💬
                            </div>
                            <h4 style="margin:0 0 6px 0; font-size:1.05rem; font-weight:700; color:var(--text-primary);">No support requests yet</h4>
                            <p style="margin:0 auto 16px auto; max-width:380px; font-size:0.84rem; color:var(--text-secondary); line-height:1.5;">
                                Have a question about an order status, shipping rate, or book availability? Submit a request on the left and our team will assist you!
                            </p>
                            <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:8px;">
                                <span style="font-size:0.75rem; padding:4px 10px; background:var(--surface-alt, #f1f5f9); color:var(--text-secondary); border-radius:20px; font-weight:600;">📦 Order Tracking</span>
                                <span style="font-size:0.75rem; padding:4px 10px; background:var(--surface-alt, #f1f5f9); color:var(--text-secondary); border-radius:20px; font-weight:600;">💳 Payment &amp; UTR</span>
                                <span style="font-size:0.75rem; padding:4px 10px; background:var(--surface-alt, #f1f5f9); color:var(--text-secondary); border-radius:20px; font-weight:600;">🚚 Shipping Info</span>
                            </div>
                        </div>
                    @endforelse

                    <div style="margin-top:14px;">
                        {{ $tickets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .support-grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 860px) {
            .support-grid {
                grid-template-columns: 1fr;
            }
        }
        html.dark .support-card-form,
        html.dark .support-ticket-item,
        html.dark .support-empty-box {
            background: var(--surface, #0f2a44) !important;
            border-color: var(--border, #1d3e5c) !important;
        }
        html.dark .support-card-form input,
        html.dark .support-card-form textarea {
            background: var(--surface-alt, #12314e) !important;
            color: var(--text-primary, #edf1fa) !important;
            border-color: var(--border, #1d3e5c) !important;
        }
        html.dark .ticket-msg-box {
            background: var(--surface-alt, #12314e) !important;
            border-color: var(--border, #1d3e5c) !important;
            color: var(--text-primary, #edf1fa) !important;
        }
        html.dark .ticket-reply-box {
            background: rgba(91, 141, 196, 0.12) !important;
            border-color: rgba(91, 141, 196, 0.3) !important;
        }
    </style>
@endsection