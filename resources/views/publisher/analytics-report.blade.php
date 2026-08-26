<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Publisher Sales Report</title>
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
    <h1>Publisher Sales Report</h1>
    <p>{{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}</p>
    <div class="summary">
        <div>Gross sales<b>₹{{ number_format($revenue, 2) }}</b></div>
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
    @if(($exportType ?? '') === 'print' || ($exportType ?? '') === 'pdf')
    <script>window.addEventListener('load', () => window.print())</script>@endif
</body>

</html>