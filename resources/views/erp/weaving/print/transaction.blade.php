<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $screen['label'] ?? 'Weaving' }} — {{ $transaction->trans_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #333; padding: 4px 6px; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h1>{{ $screen['label'] ?? 'Weaving Transaction' }}</h1>
    <p><strong>Trans #:</strong> {{ $transaction->trans_no }} &nbsp; <strong>Date:</strong> {{ optional($transaction->trans_date)->format('d/m/Y') }}</p>
    @if ($transaction->account)
        <p><strong>Party:</strong> {{ $transaction->account->name }}</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->lines as $line)
                <tr>
                    <td>{{ $line->item?->name ?? $line->description }}</td>
                    <td style="text-align:right">{{ $line->qty }}</td>
                    <td style="text-align:right">{{ $line->rate }}</td>
                    <td style="text-align:right">{{ $line->amount }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right"><strong>Total</strong></td>
                <td style="text-align:right"><strong>{{ $transaction->total_amount }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
