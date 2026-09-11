<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Analytics Report</title>
    <style>
        body{font-family:Arial,sans-serif;color:#10233d;margin:36px}h1{margin-bottom:4px}p{color:#65748a}.summary{display:flex;gap:20px;margin:22px 0;flex-wrap:wrap}.summary div{border:1px solid #d9e1ec;padding:14px;min-width:140px}.summary b{display:block;font-size:20px;margin-top:5px}table{width:100%;border-collapse:collapse;margin-top:24px}th,td{text-align:left;padding:10px;border-bottom:1px solid #d9e1ec}th{background:#eef3f8}@media print{button{display:none}}
    </style>
</head>
<body>
    @if(($exportType ?? '') === 'pdf')<button onclick="window.print()">Save as PDF</button>@endif
    <h1>Admin Analytics Report</h1>
    <p>{{ $periodLabel ?? ucfirst($period) }}</p>
    <div class="summary">
        <div>Verified revenue<b>INR {{ number_format($revenue, 2) }}</b></div>
        <div>Total orders<b>{{ number_format($orderVolume) }}</b></div>
        <div>Average paid order<b>INR {{ number_format($averageOrder, 2) }}</b></div>
        <div>Total customers<b>{{ number_format($totalCustomers) }}</b></div>
    </div>
    <h2>Top-selling books</h2>
    <table>
        <thead><tr><th>Book</th><th>Units sold</th><th>Sales (INR)</th></tr></thead>
        <tbody>
        @forelse($topBooks as $book)
            <tr><td>{{ $book->title }}</td><td>{{ $book->units }}</td><td>{{ number_format($book->sales, 2) }}</td></tr>
        @empty
            <tr><td colspan="3">No sales data for this period.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if(($exportType ?? '') === 'pdf')<script>window.addEventListener('load', () => window.print())</script>@endif
</body>
</html>
