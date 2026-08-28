<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Order Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #172033;
            padding: 24px
        }

        h1 {
            margin-bottom: 4px
        }

        p {
            color: #667085;
            margin-top: 0
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px
        }

        th,
        td {
            border: 1px solid #ccd3df;
            padding: 8px;
            text-align: left;
            font-size: 11px
        }

        th {
            background: #eef2f7;
            text-transform: uppercase
        }

        .meta {
            display: flex;
            justify-content: space-between
        }

        @media print {
            button {
                display: none
            }

            body {
                padding: 0
            }
        }
    </style>
</head>

<body>
    <div class="meta">
        <div>
            <h1>Order Report</h1>
            <p>College Street Online · Generated {{ now()->format('d M Y, h:i A') }}</p>
        </div>@if($mode !== 'excel')<button
        onclick="window.print()">{{ $mode === 'pdf' ? 'Save as PDF' : 'Print report' }}</button>@endif
    </div>
    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>@foreach($orders as $order)
            <tr>
                <td>#CSO{{ $order->id }}</td>
                <td>{{ $order->created_at->format('d M Y, H:i') }}</td>
                <td>{{ $order->customer?->name ?? '—' }}</td>
                <td>{{ $order->customer?->email ?? '—' }}</td>
                <td>{{ $order->currency_symbol }}{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</td>
                <td>{{ ucfirst($order->payment?->verified_status ?? 'No payment') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
        </tr>@endforeach
        </tbody>
    </table>@if(in_array($mode, ['print', 'pdf']))
    <script>window.addEventListener('load', () => window.print())</script>@endif
</body>

</html>