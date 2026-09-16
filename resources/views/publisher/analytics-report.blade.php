<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Publisher {{ ucfirst($report ?? 'sales') }} Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #10233d;
            margin: 36px
        }

        h1 {
            margin-bottom: 4px
        }

        p {
            color: #65748a
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px
        }

        th,
        td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #d9e1ec
        }

        th {
            background: #eef3f8
        }

        .summary {
            display: flex;
            gap: 28px;
            margin: 22px 0
        }

        .summary div {
            border: 1px solid #d9e1ec;
            padding: 14px;
            min-width: 140px
        }

        .summary b {
            display: block;
            font-size: 20px;
            margin-top: 5px
        }

        @media print {
            button {
                display: none
            }
        }
    </style>
</head>

<body>
    <button onclick="window.print()">Print / Save as PDF</button>
    <h1>Publisher {{ ucfirst($report ?? 'sales') }} Report</h1>
    <p>{{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}</p>
    @if(($report ?? 'sales') === 'sales')
    <div class="summary">
        <div>Gross sales<b>₹{{ number_format($revenue, 2) }}</b></div>
        <div>Net revenue<b>₹{{ number_format($netRevenue, 2) }}</b></div>
        <div>Orders<b>{{ $orders }}</b></div>
        <div>Units sold<b>{{ $units }}</b></div>
        <div>Average order<b>₹{{ number_format($averageOrder, 2) }}</b></div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Book</th>
                <th>ISBN</th>
                <th>Orders</th>
                <th>Units</th>
                <th>Gross sales (INR)</th>
            </tr>
        </thead>
        <tbody>@foreach($topBooks as $book)
            <tr>
                <td>{{ $book->title }}</td>
                <td>{{ $book->isbn ?: '—' }}</td>
                <td>{{ $book->orders }}</td>
                <td>{{ $book->units }}</td>
                <td>₹{{ number_format($book->revenue, 2) }}</td>
        </tr>@endforeach
        </tbody>
    </table>
    @elseif($report === 'inventory')
    <div class="summary"><div>Current stock<b>{{ $inventory->current_stock }}</b></div><div>Low stock<b>{{ $inventory->low_stock }}</b></div><div>Out of stock<b>{{ $inventory->out_of_stock }}</b></div><div>Movement<b>{{ $stockMovement }}</b></div></div>
    <table><thead><tr><th>Book</th><th>ISBN</th><th>Quantity</th><th>Low-stock threshold</th><th>Status</th></tr></thead><tbody>@foreach($inventoryBooks as $book)<tr><td>{{ $book->title }}</td><td>{{ $book->isbn }}</td><td>{{ $book->inventory?->quantity ?? 0 }}</td><td>{{ $book->inventory?->low_stock_threshold ?? 5 }}</td><td>{{ ucfirst($book->status) }}</td></tr>@endforeach</tbody></table>
    @elseif($report === 'orders')
    <div class="summary"><div>Total<b>{{ $orderAnalytics['total'] }}</b></div><div>Pending<b>{{ $orderAnalytics['pending'] }}</b></div><div>Completed<b>{{ $orderAnalytics['completed'] }}</b></div><div>Cancelled<b>{{ $orderAnalytics['cancelled'] }}</b></div></div>
    <table><thead><tr><th>Status</th><th>Orders</th></tr></thead><tbody>@foreach($statusMix as $status => $count)<tr><td>{{ str($status)->replace('_',' ')->title() }}</td><td>{{ $count }}</td></tr>@endforeach</tbody></table>
    @else
    <table><thead><tr><th>Book</th><th>ISBN</th><th>Orders</th><th>Units sold</th><th>Revenue</th></tr></thead><tbody>@foreach($topBooks as $book)<tr><td>{{ $book->title }}</td><td>{{ $book->isbn }}</td><td>{{ $book->orders }}</td><td>{{ $book->units }}</td><td>₹{{ number_format($book->revenue,2) }}</td></tr>@endforeach</tbody></table>
    @endif
    @if(($exportType ?? '') === 'print' || ($exportType ?? '') === 'pdf')
    <script>window.addEventListener('load', () => window.print())</script>@endif
</body>

</html>
