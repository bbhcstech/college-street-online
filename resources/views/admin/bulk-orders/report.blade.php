<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Bulk Request Report</title>
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
            <h1>Bulk Order Request Report</h1>
            <p>College Street Online · Generated {{ now()->format('d M Y, h:i A') }}</p>
        </div>@if($mode !== 'excel')<button
        onclick="window.print()">{{ $mode === 'pdf' ? 'Save as PDF' : 'Print report' }}</button>@endif
    </div>
    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Institution</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Submitted</th>
                <th>Quote</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>@foreach($requests as $item)
            <tr>
                <td>#BOR{{ $item->id }}</td>
                <td>{{ $item->institution_name }}</td>
                <td>{{ $item->contact_name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->phone }}</td>
                <td>{{ $item->created_at->format('d M Y, H:i') }}</td>
                <td>{{ $item->quoted_amount ? '₹' . number_format($item->quoted_amount, 2) : '—' }}</td>
                <td>{{ ucfirst($item->status) }}</td>
        </tr>@endforeach
        </tbody>
    </table>@if(in_array($mode, ['print', 'pdf']))
    <script>window.addEventListener('load', () => window.print())</script>@endif
</body>

</html>