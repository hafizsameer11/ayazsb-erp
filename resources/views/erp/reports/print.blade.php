<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #888; padding: 6px; }
        th { background: #eee; text-align: left; }
        .num { text-align: right; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Party</th>
                <th>Status</th>
                <th class="num">Qty</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['reference'] }}</td>
                    <td>{{ $row['party'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td class="num">{{ number_format((float)$row['qty'], 2) }}</td>
                    <td class="num">{{ number_format((float)$row['amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p><strong>Total Qty:</strong> {{ number_format((float)$totals['qty'], 2) }} | <strong>Total Amount:</strong> {{ number_format((float)$totals['amount'], 2) }}</p>
</body>
</html>

