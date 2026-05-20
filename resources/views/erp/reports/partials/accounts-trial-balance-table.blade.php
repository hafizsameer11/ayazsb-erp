@php
    $from_display = $from_display ?? '';
    $to_display = $to_display ?? '';
    $rows = $rows ?? [];
    $totals = $totals ?? [];
@endphp
<div class="mb-3 border border-slate-300 bg-[#f9f9f9] p-3 text-[12px]">
    <div class="font-semibold uppercase text-slate-600">Trial Balance</div>
    <div>Period: {{ $from_display }} to {{ $to_display }}</div>
</div>
<table class="w-full min-w-[900px] border-collapse text-[12px]">
    <thead>
        <tr class="bg-[#d8d8d8]">
            <th class="border border-slate-400 px-1 py-1">Account Code</th>
            <th class="border border-slate-400 px-1 py-1">Account Name</th>
            <th class="border border-slate-400 px-1 py-1 text-right">Opening Debit</th>
            <th class="border border-slate-400 px-1 py-1 text-right">Opening Credit</th>
            <th class="border border-slate-400 px-1 py-1 text-right">Debit</th>
            <th class="border border-slate-400 px-1 py-1 text-right">Credit</th>
            <th class="border border-slate-400 px-1 py-1 text-right">Closing Debit</th>
            <th class="border border-slate-400 px-1 py-1 text-right">Closing Credit</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td class="border border-slate-300 px-1 py-1 font-mono">{{ $row['account_code'] }}</td>
                <td class="border border-slate-300 px-1 py-1">{{ $row['account_name'] }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ $row['opening_debit'] > 0 ? number_format($row['opening_debit'], 2) : '' }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ $row['opening_credit'] > 0 ? number_format($row['opening_credit'], 2) : '' }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '' }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '' }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ $row['closing_debit'] > 0 ? number_format($row['closing_debit'], 2) : '' }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ $row['closing_credit'] > 0 ? number_format($row['closing_credit'], 2) : '' }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="border border-slate-300 px-2 py-2 text-slate-500">No balances in this period.</td></tr>
        @endforelse
    </tbody>
    @if ($rows !== [])
        <tfoot>
            <tr class="bg-[#f2f2f2] font-semibold">
                <td colspan="2" class="border border-slate-300 px-1 py-1">Totals</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($totals['opening_debit'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($totals['opening_credit'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($totals['debit'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($totals['credit'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($totals['closing_debit'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($totals['closing_credit'], 2) }}</td>
            </tr>
        </tfoot>
    @endif
</table>
