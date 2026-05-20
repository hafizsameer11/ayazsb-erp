@php
    $rows = $rows ?? [];
    $totals = $totals ?? [];
@endphp
<table class="w-full min-w-[820px] border-collapse text-[12px]">
    <thead>
        <tr class="bg-[#d8d8d8]">
            <th class="border border-slate-400 px-1 py-1">Date</th>
            <th class="border border-slate-400 px-1 py-1">Type</th>
            <th class="border border-slate-400 px-1 py-1">Voucher #</th>
            <th class="border border-slate-400 px-1 py-1">Party</th>
            <th class="border border-slate-400 px-1 py-1">Status</th>
            <th class="border border-slate-400 px-1 py-1 text-right">Debit</th>
            <th class="border border-slate-400 px-1 py-1 text-right">Credit</th>
            <th class="border border-slate-400 px-1 py-1 text-right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td class="border border-slate-300 px-1 py-1">{{ $row['date'] }}</td>
                <td class="border border-slate-300 px-1 py-1">{{ $row['type'] }}</td>
                <td class="border border-slate-300 px-1 py-1 font-mono">{{ $row['reference'] }}</td>
                <td class="border border-slate-300 px-1 py-1">{{ $row['party'] }}</td>
                <td class="border border-slate-300 px-1 py-1">{{ $row['status'] }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format((float) $row['debit'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format((float) $row['credit'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format((float) $row['amount'], 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="border border-slate-300 px-2 py-2 text-slate-500">No vouchers found.</td></tr>
        @endforelse
    </tbody>
    @if ($rows !== [])
        <tfoot>
            <tr class="bg-[#f2f2f2] font-semibold">
                <td colspan="5" class="border border-slate-300 px-1 py-1">Totals ({{ $totals['count'] }})</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($totals['debit'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($totals['credit'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($totals['amount'], 2) }}</td>
            </tr>
        </tfoot>
    @endif
</table>
