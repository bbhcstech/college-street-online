<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Publisher Order Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #17283e;
            padding: 24px
        }

        h1 {
            margin-bottom: 4px
        }

        p {
            color: #66758a;
            margin-top: 0
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #ccd5e0;
            text-align: left;
            font-size: 11px
        }

        th {
            background: #eef3f7
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

<body>@if($mode !== 'excel')<button
onclick="window.print()">{{ $mode === 'pdf' ? 'Save as PDF' : 'Print report' }}</button>@endif<h1>Publisher Order
        Report</h1>
    <p>Generated {{ now()->format('d M Y, h:i A') }}</p>
    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Book</th>
                <th>Qty</th>
                <th>Gross</th>
                <th>Payment</th>
                <th>Overall</th>
                <th>Fulfillment</th>
            </tr>
        </thead>
        <tbody>@foreach($items as $item) @php($price = $item->base_unit_price ?? $item->unit_price)
            <tr>
                <td>#CSO{{ $item->order_id }}</td>
                <td>{{ $item->order->created_at->format('d M Y') }}</td>
                <td>{{ $item->order->customer?->name }}</td>
                <td>{{ $item->book?->title }}</td>
                <td>{{ $item->quantity }}</td>
                <td>₹{{ number_format($item->quantity * $price, 2) }}</td>
                <td>{{ ucfirst($item->order->payment?->verified_status ?? 'No payment') }}</td>
                <td>{{ str($item->order->status)->replace('_', ' ')->title() }}</td>
                <td>{{ ucfirst($item->fulfillment_status) }}</td>
            </tr>@endforeach
        </tbody>
    </table>@if(in_array($mode, ['print', 'pdf']))
    <script>window.addEventListener('load', () => window.print())</script>@endif
</body>

</html>