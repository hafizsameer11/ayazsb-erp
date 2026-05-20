<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; color: #111; }
        table { border-collapse: collapse; width: 100%; margin-top: 12px; }
        th, td { border: 1px solid #888; padding: 5px 6px; vertical-align: top; }
        th { background: #eee; text-align: left; }
        .num { text-align: right; font-family: Consolas, monospace; }
        .meta { margin-bottom: 10px; }
        .meta div { margin: 2px 0; }
        h2 { margin: 0 0 8px; font-size: 16px; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <div class="meta">
        @if (!empty($filterMeta['from_display']) || !empty($filterMeta['to_display']))
            <div><strong>Period:</strong> {{ $filterMeta['from_display'] ?: 'Beginning' }} to {{ $filterMeta['to_display'] ?: \App\Support\ErpDate::todayDisplay() }}</div>
        @endif
        @if (!empty($filterMeta['account']))
            <div><strong>Account:</strong> {{ $filterMeta['account']->code }} — {{ $filterMeta['account']->name }}</div>
        @endif
        <div><strong>Printed:</strong> {{ now()->format('d-m-Y h:i:s A') }}</div>
    </div>
    @include($printView)
</body>
</html>
