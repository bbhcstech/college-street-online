<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tax Invoice - #CSO{{ $order->id }} | College Street Online</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #0f172a;
            background: #f8fafc;
            margin: 0;
            padding: 30px 15px;
            line-height: 1.5;
        }
        .invoice-card {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 24px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 24px;
        }
        .brand-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-section img {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: cover;
        }
        .brand-section h2 {
            margin: 0;
            font-size: 1.35rem;
            color: #1e3a8a;
        }
        .brand-section p {
            margin: 2px 0 0;
            font-size: 0.8rem;
            color: #64748b;
        }
        .invoice-meta {
            text-align: right;
        }
        .invoice-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .meta-line {
            font-size: 0.85rem;
            color: #475569;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 30px;
        }
        .details-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            font-size: 0.86rem;
        }
        .details-box h3 {
            margin: 0 0 8px 0;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .table-items th {
            background: #f1f5f9;
            color: #334155;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 14px;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
        }
        .table-items td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.88rem;
        }
        .summary-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .summary-box {
            width: 320px;
            font-size: 0.88rem;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            color: #475569;
        }
        .summary-row.total {
            border-top: 2px solid #0f172a;
            margin-top: 8px;
            padding-top: 10px;
            font-weight: 800;
            font-size: 1.1rem;
            color: #1e3a8a;
        }
        .invoice-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
        }
        @media print {
            body { background: #ffffff; padding: 0; }
            .invoice-card { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>

    <div class="invoice-card">
        <div class="invoice-header">
            <div class="brand-section">
                <img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online">
                <div>
                    <h2>College Street Online</h2>
                    <p>Kolkata's Legendary Book Market, Online</p>
                </div>
            </div>
            <div class="invoice-meta">
                <div class="invoice-title">Tax Invoice</div>
                <div class="meta-line"><strong>Invoice No:</strong> INV-CSO{{ $order->id }}</div>
                <div class="meta-line"><strong>Date:</strong> {{ $order->created_at->format('d F Y') }}</div>
                <div class="meta-line"><strong>Order Status:</strong> {{ ucfirst($order->status) }}</div>
            </div>
        </div>

        <div class="details-grid">
            <div class="details-box">
                <h3>Billed To (Customer)</h3>
                <strong>{{ $order->customer?->name ?? 'Customer' }}</strong><br>
                {{ $order->customer?->email }}<br>
                @if($order->shipping_phone)
                    Phone: {{ $order->shipping_phone }}
                @endif
            </div>
            <div class="details-box">
                <h3>Shipping &amp; Destination</h3>
                <p style="margin:0; line-height:1.4;">
                    {{ $order->shipping_address ?: 'No delivery address on record' }}<br>
                    <strong>Country:</strong> {{ $order->country ?: 'India' }} ({{ $order->currency }})
                </p>
            </div>
        </div>

        <table class="table-items">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Qty</th>
                    <th style="text-align:right;">Unit Price</th>
                    <th style="text-align:right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ Str::title($item->book?->title ?? 'Book Item') }}</strong>
                            @if($item->book?->author?->name)
                                <br><small style="color:#64748b;">by {{ $item->book->author->name }}</small>
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td style="text-align:right;">{{ $order->currency_symbol }}{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align:right;"><strong>{{ $order->currency_symbol }}{{ number_format($item->quantity * $item->unit_price, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-wrapper">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>{{ $order->currency_symbol }}{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Shipping Fee:</span>
                    <span>{{ $order->currency_symbol }}{{ number_format($order->shipping_fee, 2) }}</span>
                </div>
                @if(isset($order->tax_amount) && $order->tax_amount > 0)
                    <div class="summary-row">
                        <span>Market Tax ({{ number_format($order->tax_rate, 1) }}%):</span>
                        <span>{{ $order->currency_symbol }}{{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                @endif
                @if($order->discount_amount > 0)
                    <div class="summary-row" style="color:#078657;">
                        <span>Discount:</span>
                        <span>&minus;{{ $order->currency_symbol }}{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="summary-row total">
                    <span>Total Billed:</span>
                    <span>{{ $order->currency_symbol }}{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</span>
                </div>
                @if($order->currency !== 'INR' && isset($order->base_total_amount))
                    <div style="text-align:right; font-size:0.75rem; color:#64748b; margin-top:2px;">
                        (Base Accounting Total: ₹{{ number_format($order->base_total_amount, 2) }} INR @ 1 {{ $order->currency }} = {{ number_format($order->exchange_rate_to_inr, 2) }} INR)
                    </div>
                @endif
            </div>
        </div>

        <div class="invoice-footer">
            <p><strong>Payment Status:</strong> {{ ucfirst($order->payment?->verified_status ?? 'verified') }} &bull; <strong>Method:</strong> {{ strtoupper(str_replace('_', ' ', $order->payment?->payment_method ?? 'Manual UPI')) }} @if($order->payment?->utr_number) &bull; <strong>Ref UTR:</strong> {{ $order->payment->utr_number }} @endif</p>
            <p style="margin-top:6px;">Thank you for ordering with College Street Online!</p>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>

