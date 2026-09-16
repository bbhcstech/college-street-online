<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Inventory Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #172033; padding: 24px }
        h1 { margin-bottom: 4px }
        p { color: #667085; margin-top: 0 }
        table { width: 100%; border-collapse: collapse; margin-top: 24px }
        th, td { border: 1px solid #ccd3df; padding: 8px; text-align: left; font-size: 11px }
        th { background: #eef2f7; text-transform: uppercase }
        .meta { display: flex; justify-content: space-between; align-items: center }
        @media print { button { display: none } body { padding: 0 } }
    </style>
</head>
<body>
    <div class="meta">
        <div>
            <h1>Admin Inventory Report</h1>
            <p>College Street Online · System Master Inventory · Generated {{ now()->format('d M Y, h:i A') }}</p>
        </div>
        @if($mode !== 'excel')
            <button onclick="window.print()">{{ $mode === 'pdf' ? 'Save as PDF' : 'Print Report' }}</button>
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>Book Title</th>
                <th>ISBN</th>
                <th>Publisher</th>
                <th>Current Stock</th>
                <th>Low Threshold</th>
                <th>Stock Status</th>
                <th>Book Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($books as $book)
                @php
                    $quantity = $book->inventory?->quantity ?? 0;
                    $threshold = $book->inventory?->low_stock_threshold ?? 5;
                    $status = $quantity <= 0 ? 'Out of stock' : ($quantity <= $threshold ? 'Low stock' : 'Healthy');
                @endphp
                <tr>
                    <td>{{ $book->title }}</td>
                    <td>{{ $book->isbn }}</td>
                    <td>{{ $book->publisher?->business_name ?: ($book->publisher?->name ?? 'N/A') }}</td>
                    <td>{{ $quantity }}</td>
                    <td>{{ $threshold }}</td>
                    <td>{{ $status }}</td>
                    <td>{{ ucfirst($book->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if(in_array($mode, ['print', 'pdf']))
        <script>window.addEventListener('load', () => window.print())</script>
    @endif
</body>
</html>

